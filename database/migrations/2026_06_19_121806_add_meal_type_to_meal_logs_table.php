<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up(): void
{
    if (!Schema::hasColumn('meal_logs', 'meal_type')) {
        Schema::table('meal_logs', function (Blueprint $table) {
            $table->enum('meal_type', [
                'breakfast',
                'lunch',
                'snack',
                'dinner',
                'supper'
            ])->default('lunch')->after('quantity');
        });
    }
}

   public function down(): void
{
    if (Schema::hasColumn('meal_logs', 'meal_type')) {
        Schema::table('meal_logs', function (Blueprint $table) {
            $table->dropColumn('meal_type');
        });
    }
}
};
