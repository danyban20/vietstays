<?php

namespace App\Services;

use App\Models\Apartment;
use App\Models\Booking;
use App\Models\District;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BookingConfirmationEmailService
{
    public function __construct(
        protected VvEmailService $emailService,
    ) {}

    public function sendGuestConfirmation(Booking $booking): bool
    {
        $booking->loadMissing(['apartment']);

        $recipient = trim((string) $booking->email);
        if ($recipient === '') {
            Log::info('Booking confirmation email skipped: booking has no guest email.', [
                'booking_id' => $booking->ID,
            ]);

            return false;
        }

        $apartment = $booking->apartment ?? Apartment::query()->find($booking->apartment_id);
        $district = District::query()->find($booking->district_id);

        $tokens = [
            'FIRSTNAME' => (string) $booking->firstname,
            'LASTNAME' => (string) $booking->lastname,
            'EMAIL' => $recipient,
            'BOOKING_NUM' => (string) $booking->booking_num,
            'BOOKING_LINK' => $this->bookingLink($booking),
            'CHECK-IN_DATE' => $booking->check_in_date?->format('M j, Y') ?? '',
            'CHECK-OUT_DATE' => $booking->check_out_date?->format('M j, Y') ?? '',
            'BOOKING_TABLE' => $this->bookingTableHtml($booking, $apartment),
            'APARTMENT_NAME' => (string) (($apartment?->display_name ?: $apartment?->name) ?? ''),
            'DISTRICT_NAME' => (string) ($district?->name ?? ''),
        ];

        return $this->emailService->sendByCode('user_booking_confirmation', $recipient, $tokens);
    }

    protected function bookingLink(Booking $booking): string
    {
        $base = rtrim((string) config('app.url'), '/');

        return $base.'/?booking='.urlencode((string) $booking->booking_num);
    }

    protected function bookingTableHtml(Booking $booking, ?Apartment $apartment): string
    {
        $items = DB::table('vv_booking_items')
            ->where('booking_id', $booking->ID)
            ->orderBy('item_id')
            ->get();

        $apartmentName = e((string) (($apartment?->display_name ?: $apartment?->name) ?? ''));
        $rows = '';
        $subtotal = 0.0;

        foreach ($items as $item) {
            if (($item->item_type ?? '') !== 'booking-date') {
                continue;
            }

            $lineTotal = (float) $item->price * (int) $item->qty;
            $subtotal += $lineTotal;

            $rows .= '<tr>'
                .'<td>'.$apartmentName.'</td>'
                .'<td>'.e((string) $booking->adults).'</td>'
                .'<td>'.e((string) $booking->children).'</td>'
                .'<td>'.e((string) $item->name).'</td>'
                .'<td style="text-align:center">'.e((string) $item->qty).'</td>'
                .'<td style="text-align:right">'.e($this->formatVnd((float) $item->price)).'</td>'
                .'<td style="text-align:right">'.e($this->formatVnd($lineTotal)).'</td>'
                .'</tr>';
        }

        if ($rows === '') {
            $nights = max(1, $booking->check_in_date?->diffInDays($booking->check_out_date) ?? 1);
            $lineTotal = (float) $booking->price * $nights;
            $subtotal = $lineTotal;
            $dateLabel = ($booking->check_in_date?->format('m/d/Y') ?? '').'-'.($booking->check_out_date?->format('m/d/Y') ?? '');

            $rows = '<tr>'
                .'<td>'.$apartmentName.'</td>'
                .'<td>'.e((string) $booking->adults).'</td>'
                .'<td>'.e((string) $booking->children).'</td>'
                .'<td>'.e($dateLabel).'</td>'
                .'<td style="text-align:center">'.e((string) $nights).'</td>'
                .'<td style="text-align:right">'.e($this->formatVnd((float) $booking->price)).'</td>'
                .'<td style="text-align:right">'.e($this->formatVnd($lineTotal)).'</td>'
                .'</tr>';
        }

        foreach ($items as $item) {
            if (($item->item_type ?? '') !== 'fee') {
                continue;
            }

            $subtotal += (float) $item->price;
            $rows .= '<tr>'
                .'<td colspan="6">'.e((string) $item->name).'</td>'
                .'<td style="text-align:right">'.e($this->formatVnd((float) $item->price)).'</td>'
                .'</tr>';
        }

        $total = (float) $booking->total;

        return '<div style="overflow-x:auto">'
            .'<table style="width:100%;border-collapse:collapse" cellpadding="8">'
            .'<thead><tr>'
            .'<th style="text-align:left">Apartment</th>'
            .'<th>Adults</th><th>Children</th><th>Check-in/out</th>'
            .'<th style="text-align:center">Nights</th>'
            .'<th style="text-align:right">Daily Price</th>'
            .'<th style="text-align:right">Amount</th>'
            .'</tr></thead>'
            .'<tbody>'.$rows.'</tbody>'
            .'</table>'
            .'<table style="margin-top:12px;margin-left:auto" cellpadding="4">'
            .'<tr><td><strong>Subtotal:</strong></td><td style="text-align:right">'.e($this->formatVnd($subtotal)).'</td></tr>'
            .'<tr><td><strong>Total:</strong></td><td style="text-align:right">'.e($this->formatVnd($total)).'</td></tr>'
            .'</table>'
            .'</div>';
    }

    protected function formatVnd(float $amount): string
    {
        return number_format(round($amount)).' VND';
    }
}
