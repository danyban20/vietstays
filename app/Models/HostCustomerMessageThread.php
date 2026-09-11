<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HostCustomerMessageThread extends Model
{
    protected $table = 'vv_host_customer_message_threads';

    /**
     * Microsecond precision so unread-message comparisons against
     * host_last_read_at stay correct even when messages land in the same
     * second (Eloquent's default 'Y-m-d H:i:s' format would truncate to
     * whole seconds despite the DB columns being timestamp(6)).
     */
    protected $dateFormat = 'Y-m-d H:i:s.u';

    protected $fillable = [
        'user_id',
        'customer_key',
        'guest_token',
        'host_last_read_at',
        'last_message_at',
    ];

    protected function casts(): array
    {
        return [
            'host_last_read_at' => 'datetime',
            'last_message_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(HostCustomerMessage::class, 'thread_id');
    }
}
