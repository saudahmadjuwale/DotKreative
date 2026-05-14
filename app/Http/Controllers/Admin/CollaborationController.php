<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Collaboration;
use App\Models\MediaAsset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CollaborationController extends Controller
{
    public function index()
    {
        $items = Collaboration::latest()->paginate(20);
        return view('admin.collaborations.index', compact('items'));
    }

    public function create()
    {
        return view('admin.collaborations.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'brand_name'    => ['required','string','max:255'],
            'slug'          => ['nullable','alpha_dash','unique:collaborations,slug'],
            'mini_summary'  => ['nullable','string','max:160'],
            'description'   => ['nullable','string'],
            'location'      => ['nullable','string','max:120'],
            'year'          => ['nullable','integer','min:1900','max:2100'],
            'website_url'   => ['nullable','url'],
            'instagram_url' => ['nullable','url'],
            'brand_logo'    => ['nullable','image','max:4096'],
        ]);

        // Auto-slug when left blank
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['brand_name'].'-'.Str::random(6));
        }

        // If a logo is being uploaded, enforce quota and store it
        if ($request->hasFile('brand_logo')) {
            $capacity  = (int) config('quota.total_bytes'); // total bytes allowed
            $usedBytes = (int) MediaAsset::sum('file_bytes') + (int) Collaboration::sum('logo_bytes');

            $remaining = $capacity - $usedBytes;
            $newLogo   = $request->file('brand_logo');
            $logoSize  = (int) $newLogo->getSize();

            if ($logoSize > max(0, $remaining)) {
                return back()->withErrors([
                    'brand_logo' => 'Not enough storage. Remaining: '.formatBytes(max(0,$remaining)).', logo is '.formatBytes($logoSize).'.'
                ]);
            }

            $data['brand_logo_path'] = $newLogo->store('logos','public');
            $data['logo_bytes']      = $logoSize;
        }

        Collaboration::create($data);

        return redirect()->route('admin.collaborations.index')->with('ok','Collaboration created');
    }

    public function edit(Collaboration $collab)
    {
        // Editing happens on the Manage Media page
        return redirect()->route('admin.collaborations.media', $collab->id);
    }
    public function destroy($id)
{
    $collab = \App\Models\Collaboration::findOrFail($id);

    foreach ($collab->media as $media) {
        Storage::disk('public')->delete($media->file_path);
    }

    $collab->delete();

    return redirect()->route('admin.collaborations.index')
        ->with('success','Collaboration deleted successfully');
}
public function updateLogo(Request $request, $id)
{
    $request->validate([
        'logo' => 'required|image|max:2048'
    ]);

    $collab = \App\Models\Collaboration::findOrFail($id);

    if($collab->brand_logo_path){
        Storage::disk('public')->delete($collab->brand_logo_path);
    }

    $path = $request->file('logo')->store('logos','public');

    $collab->brand_logo_path = $path;
    $collab->save();

    return back()->with('success','Logo updated successfully');
}
    public function update(Request $request, Collaboration $collab)
    {
        $data = $request->validate([
            'brand_name'    => ['required','string','max:255'],
            'slug'          => ['nullable','alpha_dash', Rule::unique('collaborations','slug')->ignore($collab->id)],
            'mini_summary'  => ['nullable','string','max:160'],
            'description'   => ['nullable','string'],
            'location'      => ['nullable','string','max:120'],
            'year'          => ['nullable','integer','min:1900','max:2100'],
            'website_url'   => ['nullable','url'],
            'instagram_url' => ['nullable','url'],
            'brand_logo'    => ['nullable','image','max:4096'],
        ]);

        // If slug input was present but blank, keep the current slug
        if (array_key_exists('slug', $data) && blank($data['slug'])) {
            unset($data['slug']);
        }

        // Handle optional logo replacement (quota-aware)
        if ($request->hasFile('brand_logo')) {
            $capacity  = (int) config('quota.total_bytes');
            $usedBytes = (int) MediaAsset::sum('file_bytes') + (int) Collaboration::sum('logo_bytes');

            $newLogo  = $request->file('brand_logo');
            $logoSize = (int) $newLogo->getSize();

            // When replacing, you "regain" the old logo bytes
            $effectiveRemaining = ($capacity - $usedBytes) + (int) $collab->logo_bytes;

            if ($logoSize > max(0, $effectiveRemaining)) {
                return back()->withErrors([
                    'brand_logo' => 'Not enough storage to replace logo. Free: '
                        . formatBytes(max(0,$effectiveRemaining))
                        . ', new logo: ' . formatBytes($logoSize) . '.'
                ]);
            }

            // Delete old logo file if it exists
            if ($collab->brand_logo_path) {
                Storage::disk('public')->delete($collab->brand_logo_path);
            }

            // Store new logo and record its size
            $data['brand_logo_path'] = $newLogo->store('logos','public');
            $data['logo_bytes']      = $logoSize;
        }

        // ---- SLUG RENAME: move media/<old> -> media/<new> and update DB paths ----
        $oldSlug = $collab->slug;
        $newSlug = $data['slug'] ?? $oldSlug;

        if ($newSlug !== $oldSlug) {
            DB::beginTransaction();
            try {
                $disk  = Storage::disk('public');
                $oldDir = "media/{$oldSlug}";
                $newDir = "media/{$newSlug}";

                if (!$disk->exists($newDir)) {
                    $disk->makeDirectory($newDir);
                }

                $files = $disk->exists($oldDir) ? $disk->allFiles($oldDir) : [];

                foreach ($files as $oldPath) {
                    $filename   = basename($oldPath);
                    $targetPath = "{$newDir}/{$filename}";

                    // Avoid collisions
                    if ($disk->exists($targetPath)) {
                        $name = pathinfo($filename, PATHINFO_FILENAME);
                        $ext  = pathinfo($filename, PATHINFO_EXTENSION);
                        $targetPath = "{$newDir}/{$name}-".Str::random(5).($ext ? ".{$ext}" : '');
                    }

                    // Move file
                    $disk->move($oldPath, $targetPath);

                    // Update DB path for this collaboration
                    MediaAsset::where('collaboration_id', $collab->id)
                        ->where('file_path', $oldPath)
                        ->update(['file_path' => $targetPath]);
                }

                // Remove old dir if it ended up empty
                if ($disk->exists($oldDir) && count($disk->allFiles($oldDir)) === 0) {
                    $disk->deleteDirectory($oldDir);
                }

                // Ensure we save the new slug
                $data['slug'] = $newSlug;

                DB::commit();
            } catch (\Throwable $e) {
                DB::rollBack();
                Log::error('Failed to rename collaboration media folder', [
                    'old' => $oldSlug, 'new' => $newSlug, 'err' => $e->getMessage()
                ]);

                return back()->withErrors([
                    'slug' => 'We could not move media to the new slug folder. Please try again.'
                ]);
            }
        }

        $collab->update($data);

        return back()->with('ok','Collaboration updated');
    }
}
