<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * English starting text for the booking emails. Admins can rewrite them
     * (and add other languages) under Settings → Email templates; an
     * existing row is never overwritten.
     *
     * @return array<string, array{subject: string, body: string}>
     */
    private function templates(): array
    {
        return [
            'host_new_booking' => [
                'subject' => 'New booking %BOOKING_NUM% for %APARTMENT_NAME%',
                'body' => '<p>Hi %HOST_NAME%,</p><p><br></p>'
                    .'<p>You have a new booking request for <strong>%APARTMENT_NAME%</strong>.</p><p><br></p>'
                    .'<p>Guest: %FIRSTNAME% %LASTNAME%<br>Email: %EMAIL%<br>Phone: %PHONE%<br>'
                    .'Check-in: %CHECK-IN_DATE%<br>Check-out: %CHECK-OUT_DATE%<br>'
                    .'Adults: %ADULTS%, children: %CHILDREN%<br>Payment: %PAYMENT_METHOD%</p><p><br></p>'
                    .'<p>%BOOKING_TABLE%</p><p><br></p>'
                    .'<p>Please confirm or decline the booking in your dashboard:</p>'
                    .'<p><a href="%BOOKING_ADMIN_LINK%">%BOOKING_ADMIN_LINK%</a></p><p><br></p>'
                    .'<p>Thanks,</p><p>VietStays</p>',
            ],
            'guest_booking_confirmed' => [
                'subject' => 'Your booking %BOOKING_NUM% is confirmed',
                'body' => '<p>Hi %FIRSTNAME%,</p><p><br></p>'
                    .'<p>Good news: your stay at <strong>%APARTMENT_NAME%</strong> is confirmed.</p><p><br></p>'
                    .'<p>Booking reference: %BOOKING_NUM%<br>Check-in: %CHECK-IN_DATE%<br>Check-out: %CHECK-OUT_DATE%</p><p><br></p>'
                    .'<p>%BOOKING_TABLE%</p><p><br></p>'
                    .'<p>Payment is due on arrival unless you have already paid.</p><p><br></p>'
                    .'<p>See you soon,</p><p>VietStays</p>',
            ],
            'guest_booking_cancelled' => [
                'subject' => 'Your booking %BOOKING_NUM% has been cancelled',
                'body' => '<p>Hi %FIRSTNAME%,</p><p><br></p>'
                    .'<p>Your booking at <strong>%APARTMENT_NAME%</strong> for %CHECK-IN_DATE% – %CHECK-OUT_DATE% '
                    .'(reference %BOOKING_NUM%) has been cancelled.</p><p><br></p>'
                    .'<p>If you did not expect this, just reply to this email and we will help you find another stay.</p><p><br></p>'
                    .'<p>Thanks,</p><p>VietStays</p>',
            ],
            'guest_booking_updated' => [
                'subject' => 'Your booking %BOOKING_NUM% has been updated',
                'body' => '<p>Hi %FIRSTNAME%,</p><p><br></p>'
                    .'<p>Your booking has been updated. Here are the current details:</p><p><br></p>'
                    .'<p>Apartment: <strong>%APARTMENT_NAME%</strong><br>Booking reference: %BOOKING_NUM%<br>'
                    .'Check-in: %CHECK-IN_DATE%<br>Check-out: %CHECK-OUT_DATE%</p><p><br></p>'
                    .'<p>%BOOKING_TABLE%</p><p><br></p>'
                    .'<p>If anything looks wrong, just reply to this email.</p><p><br></p>'
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
