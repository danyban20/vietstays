<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Passport details for one guest on a booking.
 */
class BookingGuest extends Model
{
    protected $table = 'vv_booking_guests';

    protected $fillable = [
        'booking_id',
        'position',
        'full_name',
        'nationality',
        'passport_number',
        'date_of_birth',
        'passport_expiry',
        'photo_path',
    ];

    protected $hidden = ['photo_path'];

    protected function casts(): array
    {
        return [
            'position' => 'integer',
            'passport_number' => 'encrypted',
            'date_of_birth' => 'encrypted',
            'passport_expiry' => 'date',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'booking_id', 'ID');
    }
}
