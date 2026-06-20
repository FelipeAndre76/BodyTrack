<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ExerciseCategory;
use App\Models\Exercise;

class ExerciseSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Costas' => ['icon' => 'bi-person-arms-up', 'exercises' => [
                'Puxada Alta',
                'Remada Baixa',
                'Remada Curvada',
                'Pulldown',
                'Barra Fixa',
                'Remada Cavalinho',
                'Remada Unilateral',
                'Puxada Triângulo',
            ]],

            'Peito' => ['icon' => 'bi-heart-pulse', 'exercises' => [
                'Supino Reto',
                'Supino Inclinado',
                'Supino Declinado',
                'Crucifixo',
                'Crossover',
                'Peck Deck',
                'Flexão de Braço',
            ]],

            'Pernas' => ['icon' => 'bi-lightning-charge', 'exercises' => [
                'Leg Press',
                'Agachamento Livre',
                'Agachamento Hack',
                'Cadeira Extensora',
                'Mesa Flexora',
                'Cadeira Flexora',
                'Stiff',
                'Afundo',
                'Passada',
            ]],

            'Bíceps' => ['icon' => 'bi-activity', 'exercises' => [
                'Rosca Direta',
                'Rosca Alternada',
                'Rosca Martelo',
                'Rosca Scott',
                'Rosca Concentrada',
                'Rosca Cabo',
            ]],

            'Tríceps' => ['icon' => 'bi-arrow-down-up', 'exercises' => [
                'Tríceps Corda',
                'Tríceps Barra',
                'Tríceps Testa',
                'Tríceps Francês',
                'Paralelas',
                'Tríceps Coice',
            ]],

            'Ombros' => ['icon' => 'bi-triangle', 'exercises' => [
                'Desenvolvimento',
                'Elevação Lateral',
                'Elevação Frontal',
                'Crucifixo Inverso',
                'Arnold Press',
                'Remada Alta',
            ]],

            'Abdômen' => ['icon' => 'bi-circle', 'exercises' => [
                'Abdominal Tradicional',
                'Elevação de Pernas',
                'Prancha',
                'Abdominal Infra',
                'Abdominal Máquina',
                'Abdominal Oblíquo',
            ]],

            'Cardio' => ['icon' => 'bi-heart', 'exercises' => [
                'Esteira',
                'Bicicleta',
                'Escada',
                'Elíptico',
                'Corrida',
                'Caminhada',
            ]],

            'Trapézio' => ['icon' => 'bi-chevron-double-up', 'exercises' => [
                'Encolhimento com Halteres',
                'Encolhimento com Barra',
                'Remada Alta',
                'Face Pull',
            ]],

            'Antebraço' => ['icon' => 'bi-grip-horizontal', 'exercises' => [
                'Rosca Punho',
                'Rosca Punho Inversa',
                'Farmer Walk',
                'Pegada Estática',
            ]],
        ];

        foreach ($categories as $categoryName => $data) {
            $category = ExerciseCategory::firstOrCreate(
                ['name' => $categoryName],
                ['icon' => $data['icon']]
            );

            foreach ($data['exercises'] as $exerciseName) {
                Exercise::firstOrCreate([
                    'exercise_category_id' => $category->id,
                    'name' => $exerciseName,
                ]);
            }
        }
    }
}
