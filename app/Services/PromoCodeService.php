<?php

namespace App\Services;

use App\Models\Apartment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PromoCodeService
{
    /**
     * Resolve an active promo code the way the WordPress checkout did.
     * Returns null when the code is empty or not valid for this apartment.
     *
     * @return array{code: string, discount: float, ambassador_id: int}|null
     */
    public function resolve(string $code, int $apartmentId): ?array
    {
        $code = strtoupper(trim($code));

        if ($code === '' || ! Schema::hasTable('vv_promocodes')) {
            return null;
        }

        $row = DB::table('vv_promocodes')
            ->whereRaw('UPPER(code) = ?', [$code])
            ->first();

        if (! $row || (string) ($row->status ?? '') !== 'active') {
            return null;
        }

        $apartmentIds = json_decode((string) ($row->apartment_ids ?? '[]'), true);
        $restricted = is_array($apartmentIds) && count($apartmentIds) > 0;
        $appliesToApartment = true;

        if ($restricted) {
            $ids = array_map('intval', $apartmentIds);
            $appliesToApartment = in_array($apartmentId, $ids, true);
        }

        if (! $appliesToApartment) {
            return null;
        }

        $this->assertConversionAllows($code, (int) $row->ID);

        $discount = (float) ($row->discount ?? 0);

        if ($restricted) {
            $apartmentDiscount = (float) (Apartment::query()->whereKey($apartmentId)->value('promocode_discount') ?? 0);
            if ($apartmentDiscount > 0) {
                $discount = $apartmentDiscount;
            }
        }

        return [
            'code' => (string) $row->code,
            'discount' => $discount,
            'ambassador_id' => (int) ($row->ambassador_id ?? 0),
        ];
    }

    protected function assertConversionAllows(string $code, int $promoId): void
    {
        if (! Schema::hasTable('vv_conversions')) {
            return;
        }

        $conversion = DB::table('vv_conversions')
            ->where(function ($query) use ($code, $promoId) {
                $query->where('promo_code', $code)->orWhere('promo_code_id', $promoId);
            })
            ->first();

        if (! $conversion) {
            return;
        }

        $status = (string) ($conversion->status ?? '');

        $message = match ($status) {
            'converted' => 'This discount has already been used.',
            'cancelled' => 'This conversion invite was cancelled.',
            'expired' => 'This conversion offer has expired.',
            'sent', 'opened', 'registered' => null,
            default => 'This conversion link is not active.',
        };

        if ($message === null && ! empty($conversion->expires_at) && strtotime((string) $conversion->expires_at) < time()) {
            $message = 'This conversion offer has expired.';
        }

        if ($message !== null) {
            throw new \InvalidArgumentException($message);
        }
    }
}
