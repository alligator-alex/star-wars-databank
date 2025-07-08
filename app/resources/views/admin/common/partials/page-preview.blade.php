@php
/**
 * @var string $publicPageUrl
 * @var string|null $class
 */

$modalContentWidth = 1095.63;

$clientWidth = 1905;
$clientHeight = 947;

$ratio = round($modalContentWidth / $clientWidth, 2);
@endphp
<div style="height: {{ ceil($clientHeight * $ratio) }}px; overflow: hidden;">
    <iframe src="{{ $publicPageUrl }}"
            class="js-page-preview {{ $class ?? '' }}"
            style="width: {{ $clientWidth }}px; height: {{ $clientHeight }}px; transform: scale({{ $ratio }}); transform-origin: 0 0;"></iframe>
</div>
