<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApartmentGuestInfo extends Model
{
    protected $table = 'vv_apartment_guest_info';

    protected $fillable = [
        'apartment_id',
        'door_code',
        'door_code_note',
        'wifi_network',
        'wifi_password',
        'arrival_instructions',
        'parking_info',
        'arrival_contact_label',
        'arrival_contact_phone',
        'security_phone',
        'cleanings_per_week',
    ];

    protected function casts(): array
    {
        return [
            'door_code' => 'encrypted',
            'wifi_password' => 'encrypted',
            'cleanings_per_week' => 'integer',
        ];
    }
}
