<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApartmentAvailabilityPeriod extends Model
{
    public $timestamps = false;

    protected $table = 'vv_apartment_availability_periods';

    protected $primaryKey = 'ID';

    public $incrementing = false;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'dateadded' => 'datetime',
            'datemodified' => 'datetime',
        ];
    }

    public function apartment(): BelongsTo
    {
        return $this->belongsTo(Apartment::class, 'apartment_id', 'ID');
    }
}
