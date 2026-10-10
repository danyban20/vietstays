<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * English starting text for the emails the customer dashboard triggers.
     * Admins can rewrite them under Settings → Email templates; an existing
     * row is never overwritten.
     *
     * @return array<string, array{subject: string, body: string}>
     */
    private function templates(): array
    {
        return [
            'host_guest_cancelled' => [
                'subject' => 'Booking %BOOKING_NUM% was cancelled by the guest',
                'body' => '<p>Hi %HOST_NAME%,</p><p><br></p>'
                    .'<p>%FIRSTNAME% %LASTNAME% has cancelled their booking at <strong>%APARTMENT_NAME%</strong> '
                    .'for %CHECK-IN_DATE% – %CHECK-OUT_DATE% (reference %BOOKING_NUM%).</p><p><br></p>'
                    .'<p>The dates are open again in your calendar.</p>'
                    .'<p><a href="%BOOKING_ADMIN_LINK%">%BOOKING_ADMIN_LINK%</a></p><p><br></p>'
                    .'<p>Thanks,</p><p>VietStays</p>',
            ],
            'host_extra_cleaning_requested' => [
                'subject' => 'Extra cleaning requested for %APARTMENT_NAME%',
                'body' => '<p>Hi %HOST_NAME%,</p><p><br></p>'
                    .'<p>%FIRSTNAME% %LASTNAME% (booking %BOOKING_NUM%) has asked for an extra cleaning at '
                    .'<strong>%APARTMENT_NAME%</strong>.</p><p><br></p>'
                    .'<p>Date: %SERVICE_DATE%<br>Time: %TIME_SLOT%<br>Price: %SERVICE_PRICE%</p><p><br></p>'
                    .'<p>Please confirm or decline it on the booking:</p>'
                    .'<p><a href="%BOOKING_ADMIN_LINK%">%BOOKING_ADMIN_LINK%</a></p><p><br></p>'
                    .'<p>Thanks,</p><p>VietStays</p>',
            ],
            'guest_extra_cleaning_updated' => [
                'subject' => 'Your extra cleaning on %SERVICE_DATE% is %SERVICE_STATUS%',
                'body' => '<p>Hi %FIRSTNAME%,</p><p><br></p>'
                    .'<p>Your extra cleaning at <strong>%APARTMENT_NAME%</strong> on %SERVICE_DATE% (%TIME_SLOT%) '
                    .'is <strong>%SERVICE_STATUS%</strong>.</p><p><br></p>'
                    .'<p>You can see your stay here:</p>'
                    .'<p><a href="%MY_BOOKING_LINK%">%MY_BOOKING_LINK%</a></p><p><br></p>'
                    .'<p>Thanks,</p><p>VietStays</p>',
            ],
        ];
    }

    public function up(): void
    {
        foreach ($this->templates() as $code => $template) {
            $exists = DB::table('vv_email_templates')
                ->where('code', $code)
                ->where('locale', 'en')
                ->exists();

            if ($exists) {
                continue;
            }

            DB::table('vv_email_templates')->insert([
                'code' => $code,
                'locale' => 'en',
                'subject' => $template['subject'],
                'body' => $template['body'],
            ]);
        }
    }

    public function down(): void
    {
        DB::table('vv_email_templates')
            ->whereIn('code', array_keys($this->templates()))
            ->where('locale', 'en')
            ->delete();
    }
};
