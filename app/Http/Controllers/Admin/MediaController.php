<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Collaboration;
use App\Models\MediaAsset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManagerStatic as Image;

class MediaController extends Controller
{
    /** Show media manager for a collaboration. */
    public function index($collabId)
    {
        $collab = Collaboration::findOrFail($collabId);

        $images = MediaAsset::where('collaboration_id', $collab->id)
            ->where('type', 'image')->orderBy('id')->get();

        $videos = MediaAsset::where('collaboration_id', $collab->id)
            ->where('type', 'video')->orderBy('id')->get();

        // quota
        $mediaUsed = (int) MediaAsset::sum('file_bytes');
        $logoUsed  = (int) Collaboration::sum('logo_bytes');
        $used      = $mediaUsed + $logoUsed;
        $cap       = (int) (config('quota.total_bytes') ?? (15 * 1024 * 1024 * 1024));
        $pct       = $cap > 0 ? round(($used / $cap) * 100, 1) : 0;

        return view('admin.media.index', compact('collab','images','videos','used','cap','pct'));
    }

    /** Upload multiple files for a collaboration (batched). */
    public function store(Request $request, $collabId)
{
    $collab = \App\Models\Collaboration::findOrFail($collabId);

    $maxFileBytes = (int) (config('quota.max_file_bytes') ?? (300 * 1024 * 1024));
    $maxFileKB    = (int) ceil($maxFileBytes / 1024);
    $maxMBLabel   = (int) round($maxFileBytes / (1024 * 1024));

    $request->validate([
        'files'     => ['required'],
        'files.*'   => ['required','file','max:'.$maxFileKB,'mimes:jpg,jpeg,png,webp,gif,mp4'],
        'caption'   => ['nullable','string','max:255'],
        'alt_text'  => ['nullable','string','max:255'],
        // NEW: optional existing batch
        'batch_id'  => ['nullable','string','max:100'],
    ], [
        'files.*.max'   => "Each file must be {$maxMBLabel} MB or smaller.",
        'files.*.mimes' => 'Only JPG/PNG/WebP/GIF images or MP4 videos are allowed.',
    ]);

    $files = $request->file('files', []);
    if (!$files || !is_array($files) || count($files) === 0) {
        return back()->withErrors(['files' => 'No files selected.']);
    }

    // quota (media + logos)
    $capacity  = (int) (config('quota.total_bytes') ?? (15 * 1024 * 1024 * 1024));
    $usedBytes = (int) \App\Models\MediaAsset::sum('file_bytes') + (int) \App\Models\Collaboration::sum('logo_bytes');
    $remaining = $capacity - $usedBytes;

    $batchBytes = array_sum(array_map(fn($f)=> (int) $f->getSize(), $files));
    if ($batchBytes > max(0,$remaining)) {
        return back()->withErrors([
            'files' => 'Not enough storage. Remaining: '.$this->formatBytes(max(0,$remaining))
                     .' — Upload size: '.$this->formatBytes($batchBytes).'.'
        ]);
    }

    // NEW: use existing batch if given, else create new
    $batchId = $request->input('batch_id') ?: \Illuminate\Support\Str::uuid()->toString();

    // Start order after the current max for this batch (or 0 if new)
    $order = ((int) \App\Models\MediaAsset::where('upload_batch_id', $batchId)->max('batch_order')) + 1;

    foreach ($files as $file) {
        $ext = strtolower((string) $file->getClientOriginalExtension());
        $isVideo = ($ext === 'mp4');

        if ($isVideo) {
            $sizeBytes = (int) $file->getSize();
            if ($sizeBytes > $maxFileBytes) {
                return back()->withErrors([
                    'files' => 'This video is '.$this->formatBytes($sizeBytes).". Please upload a video below {$maxMBLabel} MB."
                ]);
            }
            try {
                $path = $file->store('media/'.$collab->slug, 'public');
            } catch (\Throwable $e) {
                \Log::error('Video upload failed', ['err'=>$e->getMessage()]);
                return back()->withErrors(['files'=>'Could not save the video file. Please try again.']);
            }
            $ratio = 1.7778; $finalSize = $sizeBytes;
        } else {
            try {
                $img = \Intervention\Image\ImageManagerStatic::make($file->getRealPath())->orientate();
                $img->resize(2560, 2560, function ($c) { $c->aspectRatio(); $c->upsize(); });
                $encoded  = $img->encode('webp', 84);

                $name     = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $safeName = \Illuminate\Support\Str::slug((string) $name) ?: 'img';
                $filename = $safeName.'-'.\Illuminate\Support\Str::random(6).'.webp';
                $path     = 'media/'.$collab->slug.'/'.$filename;

                \Illuminate\Support\Facades\Storage::disk('public')->put($path, (string) $encoded);

                $w = $img->width(); $h = max(1, $img->height());
                $ratio = round($w / $h, 4);
                $finalSize = (int) \Illuminate\Support\Facades\Storage::disk('public')->size($path);
            } catch (\Throwable $e) {
                \Log::error('Image optimize/save failed', ['err'=>$e->getMessage()]);
                return back()->withErrors(['files'=>'Could not process an image (unsupported or corrupted).']);
            }
        }

        \App\Models\MediaAsset::create([
            'collaboration_id' => $collab->id,
            'type'             => $isVideo ? 'video' : 'image',
            'file_path'        => $path,
            'file_bytes'       => $finalSize,
            'caption'          => $request->input('caption'),
            'alt_text'         => $request->input('alt_text'),
            'aspect_ratio'     => $ratio,
            'upload_batch_id'  => $batchId,
            'batch_order'      => $order++,
        ]);
    }

    return back()->with('ok','Uploaded '.count($files).' file(s).');
}


    /** Delete a media item and its file. */
    public function destroy($mediaId)
{
    $media = MediaAsset::findOrFail($mediaId);

    // delete file
    if ($media->file_path && Storage::disk('public')->exists($media->file_path)) {
        Storage::disk('public')->delete($media->file_path);
    }

    // delete thumbnail
    if ($media->thumbnail_path && Storage::disk('public')->exists($media->thumbnail_path)) {
        Storage::disk('public')->delete($media->thumbnail_path);
    }

    $collabId = $media->collaboration_id;
    $batchId  = $media->upload_batch_id;

    $media->delete();

    return redirect()
        ->route('admin.media.batch.edit', [$collabId, $batchId])
        ->with('ok', 'Media deleted successfully.');
}
    public function updateBatchOrder(Request $request, $collabId, $batchId)
    {
        foreach ($request->order as $mediaId => $orderIndex) {
            \App\Models\Media::where('id', $mediaId)
                ->update(['batch_order' => $orderIndex]);
        }

        return back()->with('ok', 'Order updated successfully!');
    }
    public function deleteBatch($collabId, $batchId)
    {
        $collab = Collaboration::findOrFail($collabId);

        // Fetch all media where upload_batch_id = batchId
        $batchItems = $collab->media()
            ->where('upload_batch_id', $batchId)
            ->get();

        if ($batchItems->isEmpty()) {
            return back()->with('error', 'No media found for this batch.');
        }

        foreach ($batchItems as $item) {

            // Delete main file
            if ($item->file_path && Storage::disk('public')->exists($item->file_path)) {
                Storage::disk('public')->delete($item->file_path);
            }

            // Delete thumbnail if exists
            if ($item->thumbnail_path && Storage::disk('public')->exists($item->thumbnail_path)) {
                Storage::disk('public')->delete($item->thumbnail_path);
            }

            // Delete DB row
            $item->delete();
        }

        return back()->with('ok', 'Batch deleted successfully.');
    }


    /** Single item edit screen. */
    public function edit($mediaId)
    {
        $media  = MediaAsset::findOrFail($mediaId);
        $collab = Collaboration::findOrFail($media->collaboration_id);
        return view('admin.media.edit', compact('media','collab'));
    }

    /** Single item update (+ optional caption sync to batch). */
    public function update(Request $request, $mediaId)
    {
        $media  = MediaAsset::findOrFail($mediaId);
        $collab = Collaboration::findOrFail($media->collaboration_id);

        $maxFileBytes = (int) (config('quota.max_file_bytes') ?? (300 * 1024 * 1024));
        $maxFileKB    = (int) ceil($maxFileBytes / 1024);
        $maxMBLabel   = (int) round($maxFileBytes / (1024 * 1024));

        $request->validate([
            'caption'      => ['nullable','string','max:255'],
            'alt_text'     => ['nullable','string','max:255'],
            'file'         => ['nullable','file','max:'.$maxFileKB,'mimes:jpg,jpeg,png,webp,gif,mp4'],
            'sync_caption' => ['nullable','boolean'],
        ], [
            'file.max'   => "The file must be {$maxMBLabel} MB or smaller.",
            'file.mimes' => 'Only JPG/PNG/WebP/GIF images or MP4 videos are allowed.',
        ]);

        $newCaption = $request->input('caption');
        $media->caption  = $newCaption;
        $media->alt_text = $request->input('alt_text');

        // no file change
        if (!$request->hasFile('file')) {
            $media->save();
            if ($media->upload_batch_id && $request->boolean('sync_caption', true)) {
                MediaAsset::where('upload_batch_id', $media->upload_batch_id)
                    ->update(['caption' => $newCaption]);
            }
            return back()->with('ok','Media updated.');
        }

        // quota effective remaining
        $capacity  = (int) (config('quota.total_bytes') ?? (15 * 1024 * 1024 * 1024));
        $usedBytes = (int) MediaAsset::sum('file_bytes') + (int) Collaboration::sum('logo_bytes');
        $effectiveRemaining = ($capacity - $usedBytes) + (int) $media->file_bytes;

        $file = $request->file('file');
        $newSize = (int) $file->getSize();
        if ($newSize > $maxFileBytes) {
            return back()->withErrors(['file' => "This file is ".$this->formatBytes($newSize).". Please upload a file below {$maxMBLabel} MB."]);
        }
        if ($newSize > max(0,$effectiveRemaining)) {
            return back()->withErrors(['file' =>
                'Not enough storage. Free: '.$this->formatBytes(max(0,$effectiveRemaining)).
                ', file: '.$this->formatBytes($newSize).'.'
            ]);
        }

        // replace
        $ext = strtolower((string) $file->getClientOriginalExtension());
        $isVideo = ($ext === 'mp4');

        try {
            if ($isVideo) {
                $newPath = $file->store('media/'.$collab->slug, 'public');
                $newType = 'video'; $newRatio = 1.7778; $finalBytes = $newSize;
            } else {
                $img = Image::make($file->getRealPath())->orientate();
                $img->resize(2560,2560,function($c){ $c->aspectRatio(); $c->upsize(); });
                $encoded  = $img->encode('webp',84);
                $name     = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $safeName = Str::slug((string) $name) ?: 'img';
                $filename = $safeName.'-'.Str::random(6).'.webp';
                $newPath  = 'media/'.$collab->slug.'/'.$filename;
                Storage::disk('public')->put($newPath,(string)$encoded);
                $w=$img->width(); $h=max(1,$img->height());
                $newType='image'; $newRatio=round($w/$h,4);
                $finalBytes=(int)Storage::disk('public')->size($newPath);
            }
        } catch (\Throwable $e) {
            \Log::error('Media replace failed', ['err'=>$e->getMessage()]);
            return back()->withErrors(['file'=>'Could not process the file. Please try again.']);
        }

        if ($media->file_path) Storage::disk('public')->delete($media->file_path);

        $media->type         = $newType;
        $media->file_path    = $newPath;
        $media->file_bytes   = $finalBytes;
        $media->aspect_ratio = $newRatio;
        $media->save();

        if ($media->upload_batch_id && $request->boolean('sync_caption', true)) {
            MediaAsset::where('upload_batch_id', $media->upload_batch_id)
                ->update(['caption' => $newCaption]);
        }

        return back()->with('ok','Media file replaced.');
    }

    /** Batch edit screen. */
    public function editBatch($collabId, $batchId)
    {
        $collab = Collaboration::findOrFail($collabId);
        $items  = MediaAsset::where('collaboration_id',$collab->id)
                    ->where('upload_batch_id',$batchId)
                    ->orderBy('batch_order')->get();

        if ($items->isEmpty()) {
            return redirect()->route('admin.collaborations.media',$collab->id)
                ->withErrors(['batch'=>'Batch not found.']);
        }

        $sharedCaption = optional($items->first())->caption;
        return view('admin.media.edit-batch', compact('collab','items','sharedCaption','batchId'));
    }

    /** Update batch (shared caption + each alt/order). */
    public function updateBatch(Request $request, $collabId, $batchId)
    {
        $collab = Collaboration::findOrFail($collabId);

        $request->validate([
            'caption' => ['nullable','string','max:255'],
            'alt_text'=> ['array'],
            'order'   => ['array'],
        ]);

        $items = MediaAsset::where('collaboration_id',$collab->id)
                 ->where('upload_batch_id',$batchId)->get();

        if ($items->isEmpty()) return back()->withErrors(['batch'=>'Batch not found.']);

        MediaAsset::where('upload_batch_id',$batchId)->update([
            'caption' => $request->input('caption')
        ]);

        $alt   = $request->input('alt_text',[]);
        $order = $request->input('order',[]);
        foreach ($items as $it) {
            $it->alt_text    = $alt[$it->id] ?? $it->alt_text;
            $it->batch_order = is_numeric($order[$it->id] ?? null) ? (int)$order[$it->id] : $it->batch_order;
            $it->save();
        }

        return redirect()->route('admin.collaborations.media',$collab->id)
            ->with('ok','Batch updated.');
    }

    /** Helper: bytes to human string. */
    private function formatBytes(int $bytes): string
    {
        if ($bytes <= 0) return '0 B';
        $units = ['B','KB','MB','GB','TB'];
        $i = (int) floor(log($bytes, 1024));
        return round($bytes / (1024 ** $i), 2).' '.$units[$i];
    }
}
