<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->foreignId('role_id')->default(2)->constrained('roles');
            $table->string('bio', 50)->nullable();
            $table->string('avatar')->nullable();
            $table->json('preferences')->nullable();
            $table->string('github_id')->nullable()->unique();
            $table->string('github_token')->nullable();
            $table->string('github_refresh_token')->nullable();
            $table->string('google_id')->nullable()->unique();
            $table->text('google_token')->nullable();
            $table->text('google_refresh_token')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};