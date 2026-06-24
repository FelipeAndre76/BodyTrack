<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Support\PortugueseTextSanitizer;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('bodytrack:fix-portuguese-text', function () {
    $paths = [
        base_path('app'),
        base_path('config'),
        base_path('database/seeders'),
        base_path('public/css'),
        base_path('resources/views'),
        base_path('routes'),
    ];

    $allowedExtensions = ['php', 'css', 'js'];
    $fixed = 0;

    foreach ($paths as $path) {
        if (!is_dir($path)) {
            continue;
        }

        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));

        foreach ($files as $file) {
            if (!$file->isFile() || !in_array($file->getExtension(), $allowedExtensions, true)) {
                continue;
            }

            $filePath = $file->getPathname();

            if (str_ends_with($filePath, 'PortugueseTextSanitizer.php')) {
                continue;
            }

            $original = file_get_contents($filePath);
            $cleaned = PortugueseTextSanitizer::fixMojibake($original);

            if ($cleaned === $original) {
                continue;
            }

            if (@file_put_contents($filePath, $cleaned) === false) {
                $this->warn("Nao foi possivel corrigir: {$filePath}");

                continue;
            }

            $fixed++;
        }
    }

    $this->info("Arquivos corrigidos: {$fixed}");
})->purpose('Corrige acentuacao quebrada em textos do BodyTrack');
