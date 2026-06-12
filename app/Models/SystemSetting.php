<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SystemSetting extends Model
{
    protected $fillable = ['key', 'value'];

    public static function get(string $key, mixed $default = null): mixed
    {
        $raw = Cache::remember("sys_setting_{$key}", 300, function () use ($key) {
            return static::where('key', $key)->value('value');
        });

        if ($raw === null) return $default;

        $decoded = json_decode($raw, true);
        return $decoded !== null ? $decoded : $raw;
    }

    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => is_string($value) ? $value : json_encode($value)]);
        Cache::forget("sys_setting_{$key}");
    }
}
