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
    if (!Schema::hasColumn('workout_items', 'notes')) {
        Schema::table('workout_items', function (Blueprint $table) {
            $table->text('notes')->nullable()->after('weight');
        });
    }
}

public function down(): void
{
    if (Schema::hasColumn('workout_items', 'notes')) {
        Schema::table('workout_items', function (Blueprint $table) {
            $table->dropColumn('notes');
        });
    }
}
};
