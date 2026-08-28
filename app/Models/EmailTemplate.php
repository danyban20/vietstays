<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class EmailTemplate extends Model
{
    public $timestamps = false;

    protected $table = 'vv_email_templates';

    protected $primaryKey = 'email_id';

    protected $fillable = [
        'code',
        'locale',
        'subject',
        'body',
    ];

    public static function humanizeCode(string $code): string
    {
        return Str::title(str_replace('_', ' ', $code));
    }

    public function displayName(): string
    {
        return static::humanizeCode($this->code);
    }
}
