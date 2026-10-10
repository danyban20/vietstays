<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingServiceRequest extends Model
{
    public const STATUSES = ['requested', 'confirmed', 'declined', 'cancelled'];

    protected $table = 'vv_booking_service_requests';

    protected $fillable = [
        'booking_id',
        'type',
        'service_date',
        'time_slot',
        'price',
        'status',
        'requested_by',
    ];

    protected function casts(): array
    {
        return [
            'service_date' => 'date',
            'price' => 'decimal:2',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'booking_id', 'ID');
    }

    public function isOpen(): bool
    {
        return in_array($this->status, ['requested', 'confirmed'], true);
    }
}
