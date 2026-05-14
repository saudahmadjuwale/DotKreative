<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gallery_media_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gallery_id')->constrained('galleries')->cascadeOnDelete();

            // "image" or "video" (use short string; no DB enums for SQLite)
            $table->string('type', 10);                      // 'image' | 'video'
            $table->string('file_path');                     // public path / URL
            $table->string('thumbnail_path')->nullable();    // for videos or heavy images
            $table->string('alt_text')->nullable();
            $table->decimal('aspect_ratio', 8, 4)->nullable(); // width/height
            $table->unsignedInteger('order_index')->default(0);
            $table->timestamps();

            $table->index('gallery_id');
            $table->index(['gallery_id', 'order_index']);
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gallery_media_assets');
    }
};
