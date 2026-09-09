<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    public $timestamps = false;

    protected $table = 'vv_bookings';

    protected $primaryKey = 'ID';

    public $incrementing = false;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'dates' => 'array',
            'extra_data' => 'array',
            'campaign_discount_desc' => 'array',
            'child_ages' => 'array',
            'check_in_date' => 'datetime',
            'check_out_date' => 'datetime',
            'price' => 'decimal:2',
            'campaign_discount' => 'decimal:2',
            'promo_code_discount' => 'decimal:2',
            'total' => 'decimal:2',
            'dateadded' => 'datetime',
            'datemodified' => 'datetime',
        ];
    }

    public function apartment(): BelongsTo
    {
        return $this->belongsTo(Apartment::class, 'apartment_id', 'ID');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class, 'district_id', 'district_id');
    }
}
