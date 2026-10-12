<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Sent to superadmins and supervisors when a host publishes an apartment
     * that is below the completion needed to go live on its own. Admins can
     * rewrite it under Settings → Email templates; an existing row is never
     * overwritten.
     */
    private const CODE = 'admin_apartment_needs_review';

    public function up(): void
    {
        $exists = DB::table('vv_email_templates')
            ->where('code', self::CODE)
            ->where('locale', 'en')
            ->exists();

        if ($exists) {
            return;
        }

        DB::table('vv_email_templates')->insert([
            'code' => self::CODE,
            'locale' => 'en',
            'subject' => '%APARTMENT_NAME% is waiting for review (%COMPLETION%% complete)',
            'body' => '<p>Hi,</p><p><br></p>'
                .'<p>%HOST_NAME% wants to publish <strong>%APARTMENT_NAME%</strong>, but the listing is only '
                .'%COMPLETION%% complete, so it is not on the website yet.</p><p><br></p>'
                .'<p>Please help the host fill it in, approve it or send it back:</p>'
                .'<p><a href="%APARTMENT_ADMIN_LINK%">%APARTMENT_ADMIN_LINK%</a></p><p><br></p>'
                .'<p>Thanks,</p><p>VietStays</p>',
        ]);
    }

    public function down(): void
    {
        DB::table('vv_email_templates')
            ->where('code', self::CODE)
            ->where('locale', 'en')
            ->delete();
    }
};
