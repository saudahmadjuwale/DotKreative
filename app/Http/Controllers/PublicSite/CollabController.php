<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\Collaboration;
use App\Models\MediaAsset;
use App\Models\GalleryMediaAsset;
use Illuminate\Support\Facades\Storage;

class CollabController extends Controller
{
    public function home(){
        $collabs = Collaboration::orderByDesc('created_at')->paginate(6);
        $randomGalleryImages = GalleryMediaAsset::whereHas('gallery', function ($q) {
        $q->where('is_published', true);
    })
    ->inRandomOrder()
    ->take(3)
    ->get();
        return view('user.home', compact('collabs','randomGalleryImages'));
    }
    public function index()
    {
        $collabs = Collaboration::orderByDesc('created_at')->paginate(12);
        return view('user.collabs.index', compact('collabs'));
    }
    public function show($slug)
    {
        $collab = Collaboration::where('slug', $slug)->firstOrFail();

        $images = MediaAsset::where('collaboration_id', $collab->id)
            ->where('type', 'image')->orderBy('id')->get();

        $videos = MediaAsset::where('collaboration_id', $collab->id)
            ->where('type', 'video')->orderBy('id')->get();

        return view('user.collabs.show', compact('collab','images','videos'));
    }
}
