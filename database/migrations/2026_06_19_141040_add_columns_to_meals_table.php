<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('meals', function (Blueprint $table) {
            $table->enum('meal_type', [
                'breakfast',
                'lunch',
                'snack',
                'dinner',
                'supper'
            ])->default('lunch')->after('user_id');

            $table->date('meal_date')->after('meal_type');

            $table->string('photo_path')->nullable()->after('meal_date');
        });
    }

    public function down(): void
    {
        Schema::table('meals', function (Blueprint $table) {
            $table->dropColumn([
                'meal_type',
                'meal_date',
                'photo_path',
            ]);
        });
    }
};
