<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('social_post_comments', function (Blueprint $table) {
            $table->foreignId('parent_id')->nullable()->after('user_id')->constrained('social_post_comments')->onDelete('cascade');
            $table->unsignedInteger('likes_count')->default(0)->after('body');
        });
    }

    public function down(): void
    {
        Schema::table('social_post_comments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('parent_id');
            $table->dropColumn('likes_count');
        });
    }
};
