<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php
        $appBase = parse_url(config('app.url'), PHP_URL_PATH) ?: '';
        $appBase = $appBase === '/' ? '' : rtrim($appBase, '/');
    @endphp
    <meta name="app-base" content="{{ $appBase }}">
    <meta name="contact-email" content="{{ config('vietstays.contact_email') }}">
    <meta name="contact-phone" content="{{ config('vietstays.contact_phone') }}">
    <title>Vietstays — Book your stay in Vietnam</title>
    @vite(['resources/css/app.css', 'resources/js/public.js'])
</head>
<body>
    <div id="app"></div>
</body>
</html>
