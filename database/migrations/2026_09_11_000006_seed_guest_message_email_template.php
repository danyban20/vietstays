<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $exists = DB::table('vv_email_templates')
            ->where('code', 'guest_message')
            ->where('locale', 'en')
            ->exists();

        if ($exists) {
            return;
        }

        DB::table('vv_email_templates')->insert([
            'code' => 'guest_message',
            'locale' => 'en',
            'subject' => 'New message from %HOST_NAME%',
            'body' => '<p>Hi,</p><p><br></p>'
                .'<p>%HOST_NAME% sent you a new message:</p><p><br></p>'
                .'<p style="padding:12px;background:#f5f2ea;border-radius:8px">%MESSAGE_PREVIEW%</p><p><br></p>'
                .'<p>Reply here:</p><p><br></p>'
                .'<p><a href="%GUEST_LINK%">%GUEST_LINK%</a></p><p><br></p>'
                .'<p>Thanks,</p><p>VietStays</p>',
        ]);
    }

    public function down(): void
    {
        DB::table('vv_email_templates')
            ->where('code', 'guest_message')
            ->where('locale', 'en')
            ->delete();
    }
};
