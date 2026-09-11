<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HostManagementCompany extends Model
{
    protected $table = 'vv_host_management_companies';

    protected $fillable = [
        'user_id',
        'name',
        'tagline',
        'legal_registered',
        'excluded',
        'pending_invites',
        'revenue_model',
        'shares',
        'included_count',
        'company_apartment_count',
    ];

    protected function casts(): array
    {
        return [
            'legal_registered' => 'boolean',
            'excluded' => 'array',
            'pending_invites' => 'array',
            'shares' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
