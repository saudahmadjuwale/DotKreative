<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('media_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('collaboration_id')->constrained('collaborations')->cascadeOnDelete();
            $table->string('type', 10);
            $table->string('file_path');
            $table->string('caption')->nullable();
            $table->string('alt_text')->nullable();
            $table->decimal('aspect_ratio', 8, 4)->nullable();
            $table->index('collaboration_id');
            $table->index('type');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media_assets');
    }
};
