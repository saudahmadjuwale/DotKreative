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
        Schema::create('collaborations', function (Blueprint $table) {
            $table->id();
            $table->string('brand_name');    
            $table->string('slug')->unique();            
            $table->string('brand_logo_path')->nullable();
            $table->string('mini_summary', 160)->nullable(); 
            $table->text('description')->nullable();      
            $table->string('location')->nullable();
            $table->unsignedSmallInteger('year')->nullable();
            $table->string('website_url')->nullable();   
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('collaborations');
    }
};
