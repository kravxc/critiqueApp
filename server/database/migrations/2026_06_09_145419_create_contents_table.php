<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('contents', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->enum('music_type', ['single', 'album', 'EP']);
            $table->string('artist');
            $table->string('genre');
            $table->date('release_date');
            $table->string('cover_image')->nullable();
            $table->string('label');
            $table->unsignedInteger('favorites_count')->default(0);
            $table->unsignedInteger('reviews_count')->default(0);
            $table->foreignId('added_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contents');
    }
};