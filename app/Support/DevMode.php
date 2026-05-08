<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\File;

class DevMode
{
    public static function enabled(): bool
    {
        return (bool) (self::state()['enabled'] ?? false);
    }

    public static function state(): array
    {
        $path = self::path();
        if (! File::exists($path)) {
            return self::defaultState();
        }

        $data = json_decode((string) File::get($path), true);
        if (! is_array($data)) {
            return self::defaultState();
        }

        return array_merge(self::defaultState(), $data);
    }

    public static function enable(User $user, ?string $message = null): void
    {
        self::write([
            'enabled' => true,
            'message' => trim((string) $message),
            'enabled_at' => now()->toDateTimeString(),
            'enabled_by_id' => $user->id,
            'enabled_by_username' => $user->username,
        ]);
    }

    public static function disable(): void
    {
        self::write(self::defaultState());
    }

    public static function isOwner(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->isDev()) {
            return true;
        }

        $ownerId = config('dev_mode.owner_id');
        if ($ownerId !== null && $ownerId !== '' && (int) $ownerId === (int) $user->id) {
            return true;
        }

        $ownerUsername = trim((string) config('dev_mode.owner_username', ''));
        if ($ownerUsername !== '' && strcasecmp($ownerUsername, (string) $user->username) === 0) {
            return true;
        }

        return false;
    }

    private static function defaultState(): array
    {
        return [
            'enabled' => false,
            'message' => '',
            'enabled_at' => null,
            'enabled_by_id' => null,
            'enabled_by_username' => null,
        ];
    }

    private static function path(): string
    {
        return (string) config('dev_mode.state_file');
    }

    private static function write(array $state): void
    {
        $path = self::path();
        File::ensureDirectoryExists(dirname($path));
        File::put($path, json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
}
