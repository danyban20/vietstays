<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HostApplication extends Model
{
    public $timestamps = false;

    protected $table = 'vv_host_applications';

    protected $primaryKey = 'ID';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'dateadded' => 'datetime',
            'datemodified' => 'datetime',
            'property_price_daily' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'legacy_wp_id');
    }

    public function laravelUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @return list<int>
     */
    public function districtIds(): array
    {
        return self::decodeJsonField($this->districts);
    }

    /**
     * @return list<int>
     */
    public function amenityIds(): array
    {
        return self::decodeJsonField($this->property_amenities);
    }

    /**
     * @return list<string>
     */
    public function platformKeys(): array
    {
        return self::decodeJsonField($this->platforms_used);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function statusHistory(): array
    {
        $history = self::decodeJsonField($this->status_history);

        return is_array($history) ? $history : [];
    }

    /**
     * @return list<mixed>
     */
    public static function decodeJsonField(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (! is_string($value) || trim($value) === '' || $value === '""') {
            return [];
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? $decoded : [];
    }

    public function isSingleProperty(): bool
    {
        return $this->applicant_type === 'single_property';
    }

    public function statusLabel(): string
    {
        return config('host_applications.statuses.'.$this->status, ucwords(str_replace('_', ' ', (string) $this->status)));
    }

    public function typeLabel(): string
    {
        return $this->isSingleProperty() ? 'One apartment' : 'Multiple apartments';
    }
}
