<?php

namespace App\Support;

class PortugueseTextSanitizer
{
    public static function fixMojibake(string $content): string
    {
        $replacements = self::replacements();

        return str_replace(array_keys($replacements), array_values($replacements), $content);
    }

    private static function replacements(): array
    {
        return [
            'ÃƒÂ§' => 'ç',
            'ÃƒÂ£' => 'ã',
            'ÃƒÂµ' => 'õ',
            'ÃƒÂ¡' => 'á',
            'ÃƒÂ©' => 'é',
            'ÃƒÂª' => 'ê',
            'ÃƒÂ­' => 'í',
            'ÃƒÂ³' => 'ó',
            'ÃƒÂº' => 'ú',
            'ÃƒÂ¢' => 'â',
            'ÃƒÂ´' => 'ô',
            'ÃƒÂ‡' => 'Ç',
            'Ã§' => 'ç',
            'Ã£' => 'ã',
            'Ãµ' => 'õ',
            'Ã¡' => 'á',
            'Ã©' => 'é',
            'Ãª' => 'ê',
            'Ã­' => 'í',
            'Ã³' => 'ó',
            'Ãº' => 'ú',
            'Ã¢' => 'â',
            'Ã´' => 'ô',
            'Ã' => 'Á',
            'Ã‰' => 'É',
            'Ã“' => 'Ó',
            'Ã‡' => 'Ç',
            'Âº' => 'º',
            'Âª' => 'ª',
        ];
    }
}
