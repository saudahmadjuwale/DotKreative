<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Gallery;
use App\Models\GalleryMediaAsset;
use App\Models\MediaAsset;        // collab media bytes (for quota)
use App\Models\Collaboration;     // collab logo bytes (for quota)
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class GalleryController extends Controller
{
    /* =========================
     * LIST + STORAGE BREAKDOWN
     * ========================= */
    public function index()
    {
        $items = Gallery::query()
            ->withCount('media')
            ->withSum(['media as folder_bytes' => function ($q) {}], 'file_bytes')
            ->latest()
            ->paginate(20);

        $capacity    = (int) config('quota.total_bytes');
        $collabUsed  = (int) MediaAsset::sum('file_bytes');
        $logoUsed    = (int) Collaboration::sum('logo_bytes');
        $galleryUsed = (int) GalleryMediaAsset::sum('file_bytes');

        return view('admin.galleries.index', compact('items','capacity','collabUsed','logoUsed','galleryUsed'));
    }
    public function updateInfo(Request $request, Gallery $gallery)
    {
        $request->validate([
            'title'   => ['required','string','max:255'],
            'caption' => ['nullable','string'],
        ]);

        $gallery->update([
            'title'   => $request->title,
            'caption' => $request->caption,
        ]);

        return back()->with('ok', 'Gallery updated successfully.');
    }

    public function create()
    {
        return view('admin.galleries.create');
    }

    /* ======================
     * CREATE (quota-aware)
     * ====================== */
    public function store(Request $r)
    {
        $data = $r->validate([
            'title'        => ['required','string','max:180'],
            'slug'         => ['nullable','alpha_dash', 'unique:galleries,slug'],
            'caption'      => ['nullable','string','max:2000'],
            'is_published' => ['nullable','boolean'],
            'media'        => ['required','array','min:1'],
            'media.*'      => ['file','max:51200'], // 50MB
        ]);

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug(($data['title'] ?? 'gallery').'-'.Str::random(6));
        }

        $files      = array_values($r->file('media', []));
        $capacity   = (int) config('quota.total_bytes');
        $usedBytes  = (int) MediaAsset::sum('file_bytes')
                    + (int) Collaboration::sum('logo_bytes')
                    + (int) GalleryMediaAsset::sum('file_bytes');

        $incoming = 0;
        foreach ($files as $f) { if ($f) $incoming += (int) $f->getSize(); }
        $remaining = $capacity - $usedBytes;

        if ($incoming > max(0,$remaining)) {
            return back()->withErrors([
                'media' => 'Not enough storage. Remaining: '.$this->fmt(max(0,$remaining)).', selected: '.$this->fmt($incoming).'.'
            ])->withInput();
        }

        $gallery = Gallery::create([
            'title'        => $data['title'],
            'slug'         => $data['slug'],
            'caption'      => $data['caption'] ?? null,
            'is_published' => (bool) ($data['is_published'] ?? false),
            'order_index'  => 0,
        ]);

        $this->storeMediaFiles($gallery, $files);

        return redirect()->route('admin.galleries.media', $gallery->id)->with('ok','Gallery created.');
    }

    /* ============
     * REDIRECT TO MEDIA PAGE FOR EDIT
     * ============ */
    public function edit(Gallery $gallery)
    {
        return redirect()->route('admin.galleries.media', $gallery->id);
    }

    /* ==================================
     * UPDATE (title/caption/publish/slug)
     * ================================== */
    public function update(Request $r, Gallery $gallery)
    {
        $data = $r->validate([
            'title'        => ['required','string','max:180'],
            'slug'         => ['nullable','alpha_dash', Rule::unique('galleries','slug')->ignore($gallery->id)],
            'caption'      => ['nullable','string','max:2000'],
            'is_published' => ['nullable','boolean'],
        ]);

        if (array_key_exists('slug', $data) && blank($data['slug'])) {
            unset($data['slug']);
        }

        $oldSlug = $gallery->slug;
        $newSlug = $data['slug'] ?? $oldSlug;

        if ($newSlug !== $oldSlug) {
            DB::beginTransaction();
            try {
                $disk  = Storage::disk('public');
                $oldDir = "gallery/{$oldSlug}";
                $newDir = "gallery/{$newSlug}";

                if (!$disk->exists($newDir)) {
                    $disk->makeDirectory($newDir);
                }

                $files = $disk->exists($oldDir) ? $disk->allFiles($oldDir) : [];

                foreach ($files as $oldPath) {
                    $filename   = basename($oldPath);
                    $targetPath = "{$newDir}/{$filename}";

                    if ($disk->exists($targetPath)) {
                        $name = pathinfo($filename, PATHINFO_FILENAME);
                        $ext  = pathinfo($filename, PATHINFO_EXTENSION);
                        $targetPath = "{$newDir}/{$name}-".Str::random(5).($ext ? ".{$ext}" : '');
                    }

                    $disk->move($oldPath, $targetPath);

                    GalleryMediaAsset::where('gallery_id', $gallery->id)
                        ->where('file_path', $oldPath)
                        ->update(['file_path' => $targetPath]);
                }

                if ($disk->exists($oldDir) && count($disk->allFiles($oldDir)) === 0) {
                    $disk->deleteDirectory($oldDir);
                }

                $data['slug'] = $newSlug;
                DB::commit();
            } catch (\Throwable $e) {
                DB::rollBack();
                Log::error('Failed to rename gallery folder', [
                    'old' => $oldSlug, 'new' => $newSlug, 'err' => $e->getMessage()
                ]);
                return back()->withErrors([
                    'slug' => 'We could not move media to the new slug folder. Please try again.'
                ]);
            }
        }

        $gallery->update([
            'title'        => $data['title'],
            'slug'         => $data['slug'] ?? $gallery->slug,
            'caption'      => $data['caption'] ?? null,
            'is_published' => (bool) ($data['is_published'] ?? $gallery->is_published),
        ]);

        return back()->with('ok','Gallery updated.');
    }

    /* ==========================
     * MEDIA PAGE + STORAGE BOX
     * ========================== */
    public function manageMedia(Gallery $gallery)
    {
        $gallery->load(['media' => fn($q)=>$q->orderBy('order_index')->orderBy('id')]);

        $capacity    = (int) config('quota.total_bytes');
        $collabUsed  = (int) MediaAsset::sum('file_bytes');
        $logoUsed    = (int) Collaboration::sum('logo_bytes');
        $galleryUsed = (int) GalleryMediaAsset::sum('file_bytes');

        return view('admin.galleries.media', compact('gallery','capacity','collabUsed','logoUsed','galleryUsed'));
    }

    /* ===================
     * UPLOAD (quota-aware)
     * =================== */
    public function uploadMedia(Request $r, Gallery $gallery)
    {
        $r->validate([
            'media'   => ['required','array','min:1'],
            'media.*' => ['file','max:51200'],
        ]);

        $files      = array_values($r->file('media', []));
        $capacity   = (int) config('quota.total_bytes');
        $usedBytes  = (int) MediaAsset::sum('file_bytes')
                    + (int) Collaboration::sum('logo_bytes')
                    + (int) GalleryMediaAsset::sum('file_bytes');

        $incoming = 0;
        foreach ($files as $f) { if ($f) $incoming += (int) $f->getSize(); }
        $remaining = $capacity - $usedBytes;

        if ($incoming > max(0,$remaining)) {
            return back()->withErrors([
                'media' => 'Not enough storage. Remaining: '.$this->fmt(max(0,$remaining)).', selected: '.$this->fmt($incoming).'.'
            ]);
        }

        $this->storeMediaFiles($gallery, $files);

        return back()->with('ok','Uploaded.');
    }

    /* ================
     * DRAG REORDER
     * ================ */
    public function updateOrder(Request $r, Gallery $gallery)
    {
        $data = $r->validate([
            'order' => ['required','array','min:1'],
            'order.*.id'          => ['required','integer','exists:gallery_media_assets,id'],
            'order.*.order_index' => ['required','integer','min:0'],
        ]);

        foreach ($data['order'] as $row) {
            GalleryMediaAsset::where('gallery_id', $gallery->id)
                ->where('id', $row['id'])
                ->update(['order_index' => (int) $row['order_index']]);
        }

        return response()->json(['ok' => true]);
    }

    /* ===============
     * PUBLISH TOGGLE
     * =============== */
    public function togglePublish(Gallery $gallery)
    {
        $gallery->is_published = ! $gallery->is_published;
        $gallery->save();
        return back()->with('ok','Publish status updated.');
    }

    /* ==================
     * DELETE ONE MEDIA
     * ================== */
    public function destroyMedia(Gallery $gallery, GalleryMediaAsset $media)
    {
        abort_unless($media->gallery_id === $gallery->id, 404);

        $disk = Storage::disk('public');
        if ($media->file_path && $disk->exists($media->file_path)) {
            $disk->delete($media->file_path);
        }
        if ($media->thumbnail_path && $disk->exists($media->thumbnail_path)) {
            $disk->delete($media->thumbnail_path);
        }
        $media->delete();

        return back()->with('ok','Deleted.');
    }

    /* ===========================
     * DELETE WHOLE GALLERY + DATA
     * =========================== */
    public function destroy(Gallery $gallery)
    {
        $slug = $gallery->slug;
        $disk = Storage::disk('public');

        $dir = "gallery/{$slug}";
        if ($disk->exists($dir)) {
            $disk->deleteDirectory($dir);
        }

        GalleryMediaAsset::where('gallery_id', $gallery->id)->delete();
        $gallery->delete();

        return redirect()->route('admin.galleries.index')->with('ok','Gallery deleted.');
    }

    /* ================
     * Helper: uploader
     * ================ */
    protected function storeMediaFiles(Gallery $gallery, array $files): void
    {
        $disk = Storage::disk('public');
        $slug = $gallery->slug;
        $base = "gallery/{$slug}";              // single folder for all gallery media
        if (!$disk->exists($base)) $disk->makeDirectory($base);

        $startOrder = (int) GalleryMediaAsset::where('gallery_id', $gallery->id)->max('order_index');
        $orderBase  = $startOrder >= 0 ? $startOrder + 1 : 0;

        foreach (array_values($files) as $i => $file) {
            if (!$file) continue;

            $mime = $file->getMimeType();
            $type = str_starts_with($mime, 'video') ? 'video' : 'image';

            // pre-move image size for ratio (temp path)
            $ratio = null;
            if ($type === 'image') {
                [$w,$h] = @getimagesize($file->getRealPath()) ?: [null,null];
                if ($w && $h) $ratio = round($w/$h, 4);
            }

            $ext  = strtolower($file->getClientOriginalExtension() ?: 'bin');
            $name = Str::random(12).'.'.$ext;

            // Save to storage/app/public/gallery/{slug}/filename
            $disk->putFileAs($base, $file, $name);
            $path = "{$base}/{$name}";

            GalleryMediaAsset::create([
                'gallery_id'     => $gallery->id,
                'type'           => $type,
                'file_path'      => $path,                     // stored relative to public disk
                'file_bytes'     => (int) $file->getSize(),
                'thumbnail_path' => null,
                'alt_text'       => null,
                'aspect_ratio'   => $ratio,
                'order_index'    => $orderBase + $i,
            ]);
        }
    }

    protected function fmt(int $bytes): string
    {
        $u=['B','KB','MB','GB','TB']; $i=$bytes>0 ? floor(log($bytes,1024)) : 0;
        return round($bytes/pow(1024,$i),2).' '.$u[$i];
    }
}
