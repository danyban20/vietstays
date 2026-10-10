<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingReview extends Model
{
    public const CATEGORIES = [
        'cleanliness' => 'Cleanliness',
        'location' => 'Location',
        'value' => 'Value for money',
        'communication' => 'Communication',
        'check_in' => 'Check-In',
        'standard' => 'Apartment Standard',
    ];

    protected $table = 'vv_booking_reviews';

    protected $fillable = [
        'booking_id',
        'apartment_id',
        'member_user_id',
        'rating',
        'category_ratings',
        'comment',
        'skipped_at',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'category_ratings' => 'array',
            'skipped_at' => 'datetime',
        ];
    }
}
