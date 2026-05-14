<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('galleries', function (Blueprint $table) {
            $table->id();
            $table->string('title');                 // required
            $table->string('slug')->unique();        // for pretty URLs
            $table->text('caption')->nullable();     // optional short text
            $table->boolean('is_published')->default(false);
            $table->unsignedInteger('order_index')->default(0); // manual sorting
            $table->timestamps();

            $table->index(['is_published', 'order_index']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('galleries');
    }
};
