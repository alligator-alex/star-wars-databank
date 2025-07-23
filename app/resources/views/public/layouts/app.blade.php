@php
use App\Modules\Databank\Common\Enums\CookieName;
use App\Modules\Databank\Public\Enums\DatabankRouteName;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\URL;

$cookieConsent = (Cookie::get(CookieName::COOKIE_CONSENT->value) === 'Y');
@endphp
<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title')</title>
    <meta name="description" content="@yield('description')">

    @if (!empty(Request::query()))
        <link rel="canonical" href="{{ URL::current() }}">
    @endif

    @vite(['resources/scss/app.scss'])

    <link rel="shortcut icon" href="{{ asset('/favicon.ico') }}" type="image/x-icon">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset("/images/favicons/apple-touch-icon.png") }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset("/images/favicons/favicon-32x32.png") }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset("/images/favicons/favicon-16x16.png") }}">
    <link rel="manifest" href="{{ asset('/images/favicons/site.webmanifest') }}">
    <meta name="theme-color" content="e5e5e5">

    @includeIf('public.layouts.partials.counters')
</head>
<body>
<div style="display:none;">
    {!! file_get_contents(public_path('/svg/sprite-factions-emblems.svg')) !!}
</div>

<div class="page-wrapper @yield('page-wrapper-class')">
    <main class="page-content">
        @if (!isset($exception) && (Request::route()?->getName() !== DatabankRouteName::HOME->value))
            @include('public.common.breadcrumbs')
        @endif
        @yield('content')

        @if (!$cookieConsent)
            <div class="cookie-modal js-cookie-modal">
                {!! __('Cookie consent') !!}
                <button class="button js-cookie-consent" title="{{ __('Give consent') }}">{{ __('Roger Roger') }}</button>
            </div>
        @endif
    </main>
</div>

@include('public.layouts.partials.footer')

@vite(['resources/js/public/app.ts'])

</body>
</html>
