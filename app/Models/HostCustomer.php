<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HostCustomer extends Model
{
    protected $table = 'vv_host_customers';

    protected $fillable = [
        'user_id',
        'legacy_host_id',
        'name',
        'email',
        'phone',
        'country',
        'country_code',
        'reserved_by',
        'note',
        'temp_ref',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
