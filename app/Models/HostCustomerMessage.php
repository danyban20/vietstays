<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HostCustomerMessage extends Model
{
    const UPDATED_AT = null;

    protected $table = 'vv_host_customer_messages';

    /**
     * Microsecond precision — see HostCustomerMessageThread::$dateFormat for why.
     */
    protected $dateFormat = 'Y-m-d H:i:s.u';

    protected $fillable = [
        'thread_id',
        'booking_id',
        'sender',
        'author',
        'body',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function thread(): BelongsTo
    {
        return $this->belongsTo(HostCustomerMessageThread::class, 'thread_id');
    }
}
