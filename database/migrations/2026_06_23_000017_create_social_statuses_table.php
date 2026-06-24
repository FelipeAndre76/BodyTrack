<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('social_statuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('caption', 300)->nullable();
            $table->string('photo_mime')->nullable();
            $table->unsignedInteger('photo_size')->nullable();
            $table->timestamp('expires_at')->index();
            $table->timestamps();
        });

        DB::statement('ALTER TABLE social_statuses ADD photo_data MEDIUMBLOB NULL AFTER photo_size');
    }

    public function down(): void
    {
        Schema::dropIfExists('social_statuses');
    }
};
