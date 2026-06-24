<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('social_bio', 220)->nullable()->after('is_active');
            $table->string('avatar_mime')->nullable()->after('social_bio');
            $table->unsignedInteger('avatar_size')->nullable()->after('avatar_mime');
        });

        DB::statement('ALTER TABLE users ADD avatar_data MEDIUMBLOB NULL AFTER avatar_size');
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['social_bio', 'avatar_mime', 'avatar_size', 'avatar_data']);
        });
    }
};
