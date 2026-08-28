<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Building extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'facilities' => 'array',
            'security_features' => 'array',
            'building_gallery' => 'array',
        ];
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class, 'district_id', 'district_id');
    }

    public function apartments(): HasMany
    {
        return $this->hasMany(Apartment::class, 'building_id');
    }
}
