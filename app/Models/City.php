<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class City extends Model
{
    public $timestamps = false;

    public $incrementing = false;

    protected $table = 'vv_cities';

    protected $primaryKey = 'city_id';

    protected $guarded = [];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    public function districts(): HasMany
    {
        return $this->hasMany(District::class, 'city_id', 'city_id');
    }
}
