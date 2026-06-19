<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Food;

class FoodSeeder extends Seeder
{
    public function run(): void
    {
        $foods = [

            [
                'name' => 'Ovo',
                'protein_per_100g' => 13,
                'carbs_per_100g' => 1.1,
                'fat_per_100g' => 11,
                'calories_per_100g' => 155,
                'unit_type' => 'unit',
                'grams_per_unit' => 50
            ],

            [
                'name' => 'Peito de Frango',
                'protein_per_100g' => 31,
                'carbs_per_100g' => 0,
                'fat_per_100g' => 3.6,
                'calories_per_100g' => 165,
                'unit_type' => 'grams'
            ],

            [
                'name' => 'Arroz Branco Cozido',
                'protein_per_100g' => 2.7,
                'carbs_per_100g' => 28,
                'fat_per_100g' => 0.3,
                'calories_per_100g' => 130,
                'unit_type' => 'grams'
            ],

            [
                'name' => 'Feijão Carioca',
                'protein_per_100g' => 4.8,
                'carbs_per_100g' => 14,
                'fat_per_100g' => 0.5,
                'calories_per_100g' => 76,
                'unit_type' => 'grams'
            ],

            [
                'name' => 'Banana',
                'protein_per_100g' => 1.1,
                'carbs_per_100g' => 23,
                'fat_per_100g' => 0.3,
                'calories_per_100g' => 89,
                'unit_type' => 'unit',
                'grams_per_unit' => 100
            ],

            [
                'name' => 'Whey Protein',
                'protein_per_100g' => 80,
                'carbs_per_100g' => 8,
                'fat_per_100g' => 6,
                'calories_per_100g' => 400,
                'unit_type' => 'unit',
                'grams_per_unit' => 30
            ],

            [
                'name' => 'Leite Desnatado',
                'protein_per_100g' => 3.4,
                'carbs_per_100g' => 5,
                'fat_per_100g' => 0.2,
                'calories_per_100g' => 35,
                'unit_type' => 'grams'
            ],

            [
                'name' => 'Queijo Mussarela',
                'protein_per_100g' => 22,
                'carbs_per_100g' => 3,
                'fat_per_100g' => 22,
                'calories_per_100g' => 300,
                'unit_type' => 'grams'
            ],

            [
                'name' => 'Tapioca',
                'protein_per_100g' => 0.2,
                'carbs_per_100g' => 86,
                'fat_per_100g' => 0,
                'calories_per_100g' => 330,
                'unit_type' => 'grams'
            ],

            [
                'name' => 'Batata Inglesa Cozida',
                'protein_per_100g' => 2,
                'carbs_per_100g' => 20,
                'fat_per_100g' => 0.1,
                'calories_per_100g' => 87,
                'unit_type' => 'grams'
            ],

        ];

        foreach ($foods as $food) {
            Food::create($food);
        }
    }
}
