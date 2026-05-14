<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GalleryMediaAsset extends Model
{
    protected $fillable = [
        'gallery_id','type','file_path','thumbnail_path','alt_text','aspect_ratio','order_index','file_bytes'
    ];

    public function gallery()
    {
        return $this->belongsTo(Gallery::class);
    }
}
