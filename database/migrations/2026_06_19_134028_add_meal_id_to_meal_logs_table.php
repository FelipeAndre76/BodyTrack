<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up(): void
{
    Schema::table('meal_logs', function (Blueprint $table) {
        $table->foreignId('meal_id')
            ->nullable()
            ->after('user_id')
            ->constrained('meals')
            ->onDelete('cascade');
    });
}

public function down(): void
{
    Schema::table('meal_logs', function (Blueprint $table) {
        $table->dropConstrainedForeignId('meal_id');
    });
}
};
