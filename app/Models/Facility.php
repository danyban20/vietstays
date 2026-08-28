<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Facility extends Model
{
    public $timestamps = false;

    protected $table = 'vv_facilities';

    protected $primaryKey = 'facility_id';

    protected $guarded = [];

    // Legacy rows may contain 0000-00-00 timestamps — keep as strings.
}
