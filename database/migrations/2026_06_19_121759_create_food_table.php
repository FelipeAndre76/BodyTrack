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
    Schema::create('foods', function (Blueprint $table) {
        $table->id();

        $table->string('name');

        $table->decimal('protein_per_100g', 6, 2)->default(0);
        $table->decimal('carbs_per_100g', 6, 2)->default(0);
        $table->decimal('fat_per_100g', 6, 2)->default(0);
        $table->decimal('calories_per_100g', 6, 2)->default(0);

        $table->enum('unit_type', ['grams', 'unit'])->default('grams');
        $table->decimal('grams_per_unit', 6, 2)->nullable();

        $table->timestamps();
    });
}

public function down(): void
{
    Schema::dropIfExists('foods');
}
};
