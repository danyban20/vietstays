<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Apartment extends Model
{
    public $timestamps = false;

    protected $table = 'vv_apartments';

    protected $primaryKey = 'ID';

    public $incrementing = false;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'building_gallery_json' => 'array',
            'seasonal_pricing' => 'array',
            'pricing' => 'array',
            'facilities' => 'array',
            'security_features' => 'array',
            'images' => 'array',
            'cleaners_checklists' => 'array',
            'house_rules_json' => 'array',
            'assigned_staff' => 'array',
            'flexible_check_in' => 'boolean',
            'allow_extension' => 'boolean',
            'cleaning_fee_enabled' => 'boolean',
            'checkin_without_host' => 'boolean',
            'airport_pickup' => 'boolean',
            'flexible_reservation' => 'boolean',
            'scooter_rental' => 'boolean',
            'price_daily' => 'decimal:2',
            'cleaning_fee' => 'decimal:2',
            'extra_cleaning_fee' => 'decimal:2',
            'scooter_rental_fee' => 'decimal:2',
            'dateadded' => 'datetime',
            'datemodified' => 'datetime',
        ];
    }

    public function building(): BelongsTo
    {
        return $this->belongsTo(Building::class, 'building_id');
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class, 'district', 'district_id');
    }

    public function availabilityPeriods(): HasMany
    {
        return $this->hasMany(ApartmentAvailabilityPeriod::class, 'apartment_id', 'ID');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'apartment_id', 'ID');
    }
}
