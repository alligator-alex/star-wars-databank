@php
/**
 * @var array<int, array<string, string>> $items
 */

$lastIndex = array_key_last($items);
@endphp
<div class="container">
    <div class="breadcrumbs wow fadeInRight">
        <span class="breadcrumbs__item wow fadeInRight"
              data-wow-delay="100ms">
            <a class="breadcrumbs__link" href="{{ route(name: 'home', absolute: false) }}">{{ __('Databank') }}</a>
        </span>
        @foreach ($items as $i => $item)
            <span class="breadcrumbs__divider wow fadeInRight"
                  data-wow-delay="{{ (($i + 1) * 100) + 100 }}ms">/</span>
            <span class="breadcrumbs__item wow fadeInRight"
                  data-wow-delay="{{ (($i + 1) * 100) + 100 }}ms">
                @if ($i !== $lastIndex)
                    <a class="breadcrumbs__link" href="{{ $item['route'] }}">{{ $item['title'] }}</a>
                @else
                    <span class="breadcrumbs__text">{{ $item['title'] }}</span>
                @endif
            </span>
        @endforeach
    </div>
</div>
