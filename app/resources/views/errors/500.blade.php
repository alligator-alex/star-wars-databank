@extends('public.layouts.app')

@section('title', '500 Internal Server Error — Star Wars Vehicles Databank')
@section('page-wrapper-class', 'page-wrapper--error page-wrapper--error-500')

@section('content')
    <section class="container">
        <div class="page-title">
            <p class="h1 error-code wow fadeInUp">500</p>
            <h1 class="wow fadeInUp" data-wow-delay="100ms">{{ __('Internal Server Error') }}</h1>
            <noindex>
                <p class="aurebesh wow fadeInUp" data-wow-delay="200ms">{{ __('Internal Server Error') }}</p>
            </noindex>
        </div>

        <div class="error-message wow fadeInUp" data-wow-delay="300ms">
            <p>{{ __('Something went wrong on our side.') }}</p>
        </div>

        <p class="error-quote wow fadeInUp" data-wow-delay="400ms">{{ __('Oh, I have a bad feeling about this.') }}</p>

        <a href="/" class="yellow-button wow fadeInUp" data-wow-delay="400ms">{{ __('Go to home page') }}</a>
    </section>
@endsection
