<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Collaboration extends Model
{
    protected $fillable = [
        'brand_name','slug','brand_logo_path','mini_summary','description',
        'location','year','website_url','instagram_url','brand_logo_path','logo_bytes',
    ];

    protected static function booted(): void
    {
        static::creating(function ($m) {
            if (!$m->slug) $m->slug = Str::slug($m->brand_name.'-'.Str::random(5));
        });
    }
    public function media()
{
    return $this->hasMany(\App\Models\MediaAsset::class, 'collaboration_id');
}
}
