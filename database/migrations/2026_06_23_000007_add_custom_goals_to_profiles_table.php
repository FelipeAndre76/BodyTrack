<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('profiles', 'custom_protein_goal')) {
                $table->unsignedSmallInteger('custom_protein_goal')->nullable()->after('meals_per_day');
            }

            if (!Schema::hasColumn('profiles', 'custom_carbs_goal')) {
                $table->unsignedSmallInteger('custom_carbs_goal')->nullable()->after('custom_protein_goal');
            }

            if (!Schema::hasColumn('profiles', 'custom_fat_goal')) {
                $table->unsignedSmallInteger('custom_fat_goal')->nullable()->after('custom_carbs_goal');
            }

            if (!Schema::hasColumn('profiles', 'custom_calories_goal')) {
                $table->unsignedSmallInteger('custom_calories_goal')->nullable()->after('custom_fat_goal');
            }

            if (!Schema::hasColumn('profiles', 'custom_water_goal')) {
                $table->unsignedSmallInteger('custom_water_goal')->nullable()->after('custom_calories_goal');
            }
        });
    }

    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            foreach ([
                'custom_water_goal',
                'custom_calories_goal',
                'custom_fat_goal',
                'custom_carbs_goal',
                'custom_protein_goal',
            ] as $column) {
                if (Schema::hasColumn('profiles', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
