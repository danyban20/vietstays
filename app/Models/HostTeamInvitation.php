<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HostTeamInvitation extends Model
{
    protected $table = 'vv_host_team_invitations';

    protected $fillable = [
        'user_id',
        'legacy_host_id',
        'team_type',
        'name',
        'email',
        'phone',
        'role',
        'role_key',
        'area',
        'permissions',
        'pay_rate',
        'pay_setup',
        'org',
        'token',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'permissions' => 'array',
            'sent_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
