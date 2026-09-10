<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HostCustomerNote extends Model
{
    protected $table = 'vv_host_customer_notes';

    protected $fillable = [
        'user_id',
        'customer_key',
        'author',
        'text',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
