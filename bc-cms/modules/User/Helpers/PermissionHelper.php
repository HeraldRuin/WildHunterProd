<?php

namespace Modules\User\Helpers;

use Illuminate\Support\Facades\Cache;

class PermissionHelper
{
    private const CACHE_KEY = 'permissions.all';
    private const CACHE_TTL = 604800; // 7 дней

    public static function add($permissions): void
    {
        if (!is_array($permissions)) {
            $permissions = [$permissions];
        }

        $all = self::all();

        foreach ($permissions as $permission) {
            if (!in_array($permission, $all, true)) {
                $all[] = $permission;
            }
        }

        Cache::put(self::CACHE_KEY, $all, self::CACHE_TTL);
    }

    public static function all(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return config('permissions', []);
        });
    }

    public static function find(string $permission): bool
    {
        return in_array($permission, self::all(), true);
    }

    public static function refresh(): array
    {
        Cache::forget(self::CACHE_KEY);

        return self::all();
    }
}
