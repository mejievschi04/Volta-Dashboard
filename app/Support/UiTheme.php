<?php

namespace App\Support;

use Illuminate\Support\Facades\Cookie;

class UiTheme
{
    public const DEFAULT = 'dark';

    public const COOKIE = 'volta_theme';

    public const THEMES = [
        'dark' => 'Dark (default)',
        'dark-red' => 'Dark Red',
        'neon-glass' => 'Neon Glass',
    ];

    public static function normalize(?string $theme): string
    {
        return array_key_exists((string) $theme, self::THEMES) ? (string) $theme : self::DEFAULT;
    }

    /** Tema utilizatorului autentificat; pentru vizitatori, ultima temă salvată pe acest browser. */
    public static function current(): string
    {
        $user = auth()->user();
        if ($user) {
            return self::normalize($user->theme ?? null);
        }

        return self::normalize(request()->cookie(self::COOKIE));
    }

    public static function remember(string $theme): void
    {
        Cookie::queue(self::COOKIE, self::normalize($theme), 60 * 24 * 365);
    }
}
