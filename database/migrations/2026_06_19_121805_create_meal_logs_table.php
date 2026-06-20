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
    Schema::create('meal_logs', function (Blueprint $table) {
        $table->id();

        $table->foreignId('user_id')
            ->constrained()
            ->onDelete('cascade');

        $table->foreignId('food_id')
            ->constrained('foods')
            ->onDelete('cascade');

        $table->decimal('quantity', 8, 2);

        $table->decimal('protein', 8, 2)->default(0);
        $table->decimal('carbs', 8, 2)->default(0);
        $table->decimal('fat', 8, 2)->default(0);
        $table->decimal('calories', 8, 2)->default(0);

        $table->date('meal_date');

        $table->string('photo_path')->nullable();

        $table->timestamps();
    });
}

public function down(): void
{
    Schema::dropIfExists('meal_logs');
}
};
