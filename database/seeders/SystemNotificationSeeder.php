<?php

namespace Database\Seeders;

use App\Models\SystemNotification;
use Illuminate\Database\Seeder;

class SystemNotificationSeeder extends Seeder
{
    public function run(): void
    {
        SystemNotification::updateOrCreate(
            ['title' => 'Nova leitura nutricional por foto'],
            [
                'message' => 'Agora você pode cadastrar alimentos enviando uma foto da tabela nutricional.',
                'type' => 'success',
                'icon' => 'bi-camera',
                'link_label' => 'Testar agora',
                'link_url' => '/nutrition',
                'is_active' => true,
                'starts_at' => now(),
                'ends_at' => now()->addDays(30),
            ]
        );
    }
}
