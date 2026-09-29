<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'status',
        'company_number',
        'rejection_reason',
        'reviewed_by',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'legal_registered' => 'boolean',
            'excluded' => 'array',
            'pending_invites' => 'array',
            'shares' => 'array',
            'reviewed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Every host currently linked to this company (the submitting host is
     * linked to their own company as soon as it's created).
     */
    public function linkedHosts(): HasMany
    {
        return $this->hasMany(User::class, 'management_company_id');
    }
}
