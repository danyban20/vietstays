<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'legacy_wp_id', 'role', 'display_name', 'phone', 'admin_locale', 'management_company_id'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'legacy_wp_id' => 'integer',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'superadmin';
    }

    public function isSupervisor(): bool
    {
        return $this->role === 'supervisor';
    }

    public function isPartner(): bool
    {
        return $this->role === 'partner';
    }

    public function isHost(): bool
    {
        return $this->role === 'host';
    }

    /**
     * Roles with a host/admin dashboard. An allowlist on purpose: dashboard
     * controllers scope data for host/partner and show everything to other
     * roles, so a role missing from here (member, and staff/ambassador until
     * their dashboards exist) must never reach them.
     */
    public const DASHBOARD_ROLES = ['superadmin', 'supervisor', 'partner', 'host'];

    public function canUseDashboard(): bool
    {
        return in_array($this->role, self::DASHBOARD_ROLES, true);
    }

    /**
     * A customer account from the public site: books stays, never sees the
     * host dashboard.
     */
    public function isMember(): bool
    {
        return $this->role === 'member';
    }

    /**
     * True for any non-admin owner of apartments/bookings (partner or host).
     * Use this instead of isPartner() alone when scoping data to "my own
     * resources" — isPartner() misses the equally-valid 'host' role.
     */
    public function isOperator(): bool
    {
        return $this->isPartner() || $this->isHost();
    }

    public function managementCompany(): BelongsTo
    {
        return $this->belongsTo(HostManagementCompany::class, 'management_company_id');
    }
}
