<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Gallery;
use App\Models\GalleryMediaAsset;
class GalleryPageController extends Controller
{
   public function index()
    {
        $galleries = Gallery::with(['media' => function($q){
            $q->orderBy('order_index');
        }])->published()->get();

        return view('user.galleries', compact('galleries'));
    }


}
