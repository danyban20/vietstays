<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SecurityFeature extends Model
{
    public $timestamps = false;

    protected $table = 'vv_security_features';

    protected $primaryKey = 'security_feature_id';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'dateadded' => 'datetime',
            'datemodified' => 'datetime',
        ];
    }
}
