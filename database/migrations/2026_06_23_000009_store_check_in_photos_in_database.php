<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('weekly_check_ins', function (Blueprint $table) {
            if (!Schema::hasColumn('weekly_check_ins', 'photo_mime')) {
                $table->string('photo_mime')->nullable()->after('photo_path');
            }

            if (!Schema::hasColumn('weekly_check_ins', 'photo_size')) {
                $table->unsignedInteger('photo_size')->nullable()->after('photo_mime');
            }
        });

        if (!Schema::hasColumn('weekly_check_ins', 'photo_data')) {
            DB::statement('ALTER TABLE weekly_check_ins ADD photo_data MEDIUMBLOB NULL AFTER photo_size');
        }
    }

    public function down(): void
    {
        Schema::table('weekly_check_ins', function (Blueprint $table) {
            if (Schema::hasColumn('weekly_check_ins', 'photo_data')) {
                $table->dropColumn('photo_data');
            }

            if (Schema::hasColumn('weekly_check_ins', 'photo_size')) {
                $table->dropColumn('photo_size');
            }

            if (Schema::hasColumn('weekly_check_ins', 'photo_mime')) {
                $table->dropColumn('photo_mime');
            }
        });
    }
};
