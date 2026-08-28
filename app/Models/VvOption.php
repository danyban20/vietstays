<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VvOption extends Model
{
    public $timestamps = false;

    protected $table = 'vv_options';

    protected $fillable = [
        'option_name',
        'option_value',
        'autoload',
    ];

    public static function getValue(string $name, mixed $default = null): mixed
    {
        $option = static::query()->where('option_name', $name)->first();

        if ($option === null || $option->option_value === null) {
            return $default;
        }

        return $option->option_value;
    }

    public static function getJson(string $name, array $default = []): array
    {
        $value = static::getValue($name);

        if (! is_string($value) || $value === '') {
            return $default;
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? $decoded : $default;
    }

    public static function setValue(string $name, mixed $value, string $autoload = 'auto'): void
    {
        static::query()->updateOrCreate(
            ['option_name' => $name],
            [
                'option_value' => is_array($value) ? json_encode($value) : (string) $value,
                'autoload' => $autoload,
            ],
        );
    }
}
