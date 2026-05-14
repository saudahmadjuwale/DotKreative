<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MediaAsset extends Model
{
    protected $fillable = [
        'collaboration_id','type','file_path','caption','alt_text','aspect_ratio','file_bytes', 'upload_batch_id','batch_order',
    ];
}
