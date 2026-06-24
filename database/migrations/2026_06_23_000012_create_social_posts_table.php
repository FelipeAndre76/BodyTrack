<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('social_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('category', 30)->default('treino');
            $table->text('caption')->nullable();
            $table->string('photo_mime')->nullable();
            $table->unsignedInteger('photo_size')->nullable();
            $table->unsignedInteger('likes_count')->default(0);
            $table->unsignedInteger('comments_count')->default(0);
            $table->timestamps();
        });

        DB::statement('ALTER TABLE social_posts ADD photo_data MEDIUMBLOB NULL AFTER photo_size');
    }

    public function down(): void
    {
        Schema::dropIfExists('social_posts');
    }
};
