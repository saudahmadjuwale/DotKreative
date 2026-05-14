<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Gallery extends Model
{
    protected $fillable = ['title','slug','caption','is_published','order_index'];

    protected $casts = [
        'is_published' => 'boolean',
    ];

    public function media()
    {
        return $this->hasMany(GalleryMediaAsset::class)->orderBy('order_index');
    }

    // Auto-slug on create if not provided
    protected static function booted()
    {
        static::creating(function ($gallery) {
            if (empty($gallery->slug)) {
                $base = Str::slug($gallery->title);
                $slug = $base;
                $i = 1;
                while (static::where('slug', $slug)->exists()) {
                    $slug = $base.'-'.$i++;
                }
                $gallery->slug = $slug;
            }
        });
    }

    public function scopePublished($q)
    {
        return $q->where('is_published', true)->orderBy('order_index');
    }
}
