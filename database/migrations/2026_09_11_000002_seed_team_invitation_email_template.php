<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $exists = DB::table('vv_email_templates')
            ->where('code', 'team_invitation')
            ->where('locale', 'en')
            ->exists();

        if ($exists) {
            return;
        }

        DB::table('vv_email_templates')->insert([
            'code' => 'team_invitation',
            'locale' => 'en',
            'subject' => '%INVITER_NAME% invited you to join their Vietstays team',
            'body' => '<p>Hi %NAME%,</p><p><br></p>'
                .'<p>%INVITER_NAME% has invited you to join their team on Vietstays as <strong>%ROLE%</strong>'
                .'%AREA_LINE%.</p><p><br></p>'
                .'<p>Accept the invitation to get started:</p><p><br></p>'
                .'<p><a href="%ACCEPT_LINK%">%ACCEPT_LINK%</a></p><p><br></p>'
                .'<p>If you were not expecting this invitation, you can safely ignore this email.</p><p><br></p>'
                .'<p>Thanks,</p><p>VietStays</p>',
        ]);
    }

    public function down(): void
    {
        DB::table('vv_email_templates')
            ->where('code', 'team_invitation')
            ->where('locale', 'en')
            ->delete();
    }
};
