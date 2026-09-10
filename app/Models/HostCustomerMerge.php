<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HostCustomerMerge extends Model
{
    protected $table = 'vv_host_customer_merges';

    protected $fillable = [
        'user_id',
        'merged_key',
        'keep_key',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
