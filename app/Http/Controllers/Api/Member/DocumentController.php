<?php

namespace App\Http\Controllers\Api\Member;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\GuestStayService;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * PDF booking confirmation and receipt for the guest.
 */
class DocumentController extends Controller
{
    public function __construct(
        protected GuestStayService $stays,
    ) {}

    public function show(Request $request, int $booking, string $type): Response
    {
        abort_unless(in_array($type, ['confirmation', 'receipt'], true), 404);

        $model = $this->stays->findForUser($request->user(), $booking);
        $model->load(['serviceRequests']);
        $detail = $this->stays->detail($model);

        abort_unless($detail['documents'][$type] ?? false, 404);

        $html = view('documents.booking', [
            'type' => $type,
            'booking' => $model,
            'detail' => $detail,
            'money' => fn (float $amount) => $this->stays->money($amount),
            'contactEmail' => config('vietstays.contact_email'),
            'contactPhone' => config('vietstays.contact_phone'),
            'issued' => $this->stays->now()->format('M j, Y'),
        ])->render();

        $options = new Options;
        $options->setIsRemoteEnabled(false);
        $options->setDefaultFont('DejaVu Sans');

        $pdf = new Dompdf($options);
        $pdf->loadHtml($html, 'UTF-8');
        $pdf->setPaper('A4');
        $pdf->render();

        $filename = 'vietstays-'.$type.'-'.$this->reference($model).'.pdf';

        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Cache-Control' => 'private, no-store',
        ]);
    }

    protected function reference(Booking $booking): string
    {
        return preg_replace('/[^A-Za-z0-9-]/', '', (string) ($booking->booking_num ?: $booking->ID)) ?: (string) $booking->ID;
    }
}
