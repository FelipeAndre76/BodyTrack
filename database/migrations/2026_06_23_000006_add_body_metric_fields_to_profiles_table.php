<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('profiles', 'gender')) {
                $table->string('gender')->nullable()->after('birth_date');
            }

            if (!Schema::hasColumn('profiles', 'activity_level')) {
                $table->string('activity_level')->default('light')->after('gender');
            }

            if (!Schema::hasColumn('profiles', 'nutrition_goal')) {
                $table->string('nutrition_goal')->nullable()->after('activity_level');
            }

            if (!Schema::hasColumn('profiles', 'meals_per_day')) {
                $table->unsignedTinyInteger('meals_per_day')->default(4)->after('nutrition_goal');
            }
        });
    }

    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            if (Schema::hasColumn('profiles', 'meals_per_day')) {
                $table->dropColumn('meals_per_day');
            }

            if (Schema::hasColumn('profiles', 'nutrition_goal')) {
                $table->dropColumn('nutrition_goal');
            }

            if (Schema::hasColumn('profiles', 'activity_level')) {
                $table->dropColumn('activity_level');
            }

            if (Schema::hasColumn('profiles', 'gender')) {
                $table->dropColumn('gender');
            }
        });
    }
};
