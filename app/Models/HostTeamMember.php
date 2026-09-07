<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HostTeamMember extends Model
{
    protected $table = 'vv_host_team_members';

    protected $fillable = [
        'user_id',
        'legacy_host_id',
        'team_type',
        'name',
        'email',
        'phone',
        'org',
        'area',
        'func',
        'link',
        'member_type',
        'roles',
        'permissions',
        'apartments',
        'bookings90',
        'gross90',
        'out_pct',
        'pooled',
        'rating',
        'tasks_week',
        'avg_time',
        'avg_time_warn',
        'guest_info',
        'status',
        'avatar_color',
    ];

    protected function casts(): array
    {
        return [
            'roles' => 'array',
            'permissions' => 'array',
            'pooled' => 'boolean',
            'avg_time_warn' => 'boolean',
            'guest_info' => 'boolean',
            'rating' => 'decimal:1',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
