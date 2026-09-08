<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class District extends Model
{
    public $timestamps = false;

    public $incrementing = false;

    protected $table = 'vv_districts';

    protected $primaryKey = 'district_id';

    protected $guarded = [];

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class, 'city_id', 'city_id');
    }

    public function apartments(): HasMany
    {
        return $this->hasMany(Apartment::class, 'district', 'district_id');
    }

    public function buildings(): HasMany
    {
        return $this->hasMany(Building::class, 'district_id', 'district_id');
    }
}
