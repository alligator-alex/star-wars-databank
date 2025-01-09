@extends('public.layouts.app')

@section('title', '404 Not Found — Star Wars Vehicles Databank')
@section('page-wrapper-class', 'page-wrapper--error page-wrapper--error-404')

@section('content')
<section class="container">
    <div class="page-title">
        <p class="h1 error-code wow fadeInUp">404</p>
        <h1 class="wow fadeInUp" data-wow-delay="100ms">{{ __('Not Found') }}</h1>
        <noindex>
            <p class="aurebesh wow fadeInUp" data-wow-delay="200ms">{{ __('Not Found') }}</p>
        </noindex>
    </div>

    <div class="error-message wow fadeInUp" data-wow-delay="300ms">
        <p>{{ __('I hate to say it but it looks like the page you\'re searching for doesn\'t exist.') }}</p>
    </div>

    <p class="error-quote wow fadeInUp" data-wow-delay="400ms">{{ __('Impossible, perhaps the archives are incomplete.') }}</p>

    <a href="/" class="yellow-button wow fadeInUp" data-wow-delay="400ms">{{ __('Go to home page') }}</a>
</section>
@endsection
