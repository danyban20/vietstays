<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>{{ $type === 'receipt' ? 'Receipt' : 'Booking confirmation' }} {{ $detail['booking_num'] }}</title>
<style>
    @page { margin: 36px 42px; }
    body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #33403f; line-height: 1.5; }
    .brand { font-size: 20px; font-weight: bold; color: #0B3F41; letter-spacing: 1px; }
    .muted { color: #6B7676; }
    .head { border-bottom: 2px solid #0B3F41; padding-bottom: 12px; margin-bottom: 18px; }
    .head table { width: 100%; }
    h1 { font-size: 16px; color: #0B3F41; margin: 0 0 4px; }
    h2 { font-size: 12px; color: #0B3F41; margin: 18px 0 6px; text-transform: uppercase; letter-spacing: .5px; }
    table.grid { width: 100%; border-collapse: collapse; }
    table.grid td { padding: 6px 0; vertical-align: top; }
    table.lines { width: 100%; border-collapse: collapse; margin-top: 4px; }
    table.lines td { padding: 7px 0; border-bottom: 1px solid #e3ddcb; }
    table.lines td.amount { text-align: right; white-space: nowrap; }
    table.lines tr.total td { border-bottom: none; border-top: 2px solid #0B3F41; font-weight: bold; font-size: 13px; color: #0B3F41; }
    .discount { color: #27AE60; }
    .status { display: inline-block; padding: 2px 8px; border-radius: 4px; background: #E8F1F1; color: #0B3F41; font-weight: bold; }
    .note { margin-top: 18px; padding: 10px 12px; background: #f6f2e6; border-radius: 6px; }
    .foot { margin-top: 28px; font-size: 10px; color: #6B7676; border-top: 1px solid #e3ddcb; padding-top: 10px; }
</style>
</head>
<body>
@php
    $price = $detail['price'];
    $paysOnSite = $price['payment_method'] === 'onsite';
@endphp
<div class="head">
    <table>
        <tr>
            <td><div class="brand">VIETSTAYS</div></td>
            <td style="text-align:right" class="muted">
                Issued {{ $issued }}<br>
                Booking ID <strong>{{ $detail['booking_num'] }}</strong>
            </td>
        </tr>
    </table>
</div>

<h1>{{ $type === 'receipt' ? 'Receipt' : 'Booking confirmation' }}</h1>
<span class="status">{{ $detail['badge']['label'] }}</span>

<h2>Guest</h2>
<table class="grid">
    <tr>
        <td style="width:50%">{{ $detail['guest_name'] ?: '—' }}<br><span class="muted">{{ $booking->email }}</span></td>
        <td>
            {{ $detail['adults'] }} {{ \Illuminate\Support\Str::plural('adult', $detail['adults']) }}@if ($detail['children'] > 0), {{ $detail['children'] }} {{ \Illuminate\Support\Str::plural('child', $detail['children']) }}@endif
        </td>
    </tr>
</table>

<h2>Stay</h2>
<table class="grid">
    <tr>
        <td colspan="2"><strong>{{ $detail['apartment']['name'] }}</strong><br><span class="muted">{{ $detail['apartment_detail']['address'] }}</span></td>
    </tr>
    <tr>
        <td style="width:50%">Check-in<br><strong>{{ \Carbon\Carbon::parse($detail['check_in'])->format('D, M j, Y') }}</strong> from {{ $detail['check_in_time'] }}</td>
        <td>Check-out<br><strong>{{ \Carbon\Carbon::parse($detail['check_out'])->format('D, M j, Y') }}</strong> by {{ $detail['check_out_time'] }}</td>
    </tr>
</table>

<h2>{{ $type === 'receipt' ? 'Charges' : 'Price' }}</h2>
<table class="lines">
    @foreach ($price['lines'] as $line)
        <tr>
            <td>{{ $line['label'] }}</td>
            <td class="amount {{ $line['amount'] < 0 ? 'discount' : '' }}">{{ $line['amount'] < 0 ? '−' : '' }}{{ $money(abs($line['amount'])) }}</td>
        </tr>
    @endforeach
    <tr class="total">
        <td>{{ $price['total_label'] }}</td>
        <td class="amount">{{ $money($price['total']) }}</td>
    </tr>
</table>

<div class="note">
    @if ($type === 'receipt')
        Payment method: {{ $paysOnSite ? 'Paid at the property' : 'Card' }}.
        This receipt confirms the stay and the amount charged for it. It is not a VAT invoice; ask us if you need one.
    @elseif ($booking->status === 'pending')
        Your booking request has been sent to the host. You will get an email as soon as it is confirmed.
        {{ $paysOnSite ? 'Payment is due on arrival.' : '' }}
    @else
        Your stay is confirmed. {{ $paysOnSite ? 'Payment is due on arrival at the property.' : '' }}
        Door code and Wi-Fi details appear in your Vietstays account shortly before check-in.
    @endif
</div>

<div class="foot">
    Vietstays · {{ config('app.url') }}
    @if ($contactEmail) · {{ $contactEmail }} @endif
    @if ($contactPhone) · {{ $contactPhone }} @endif
</div>
</body>
</html>
