<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('foods', function (Blueprint $table) {
            $table->string('label_photo_path')->nullable()->after('source');
        });

        Schema::table('nutrition_scan_logs', function (Blueprint $table) {
            $table->string('label_photo_path')->nullable()->after('food_name');
        });
    }

    public function down(): void
    {
        Schema::table('foods', function (Blueprint $table) {
            $table->dropColumn('label_photo_path');
        });

        Schema::table('nutrition_scan_logs', function (Blueprint $table) {
            $table->dropColumn('label_photo_path');
        });
    }
};
