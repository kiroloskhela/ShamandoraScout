@php
    $fontLocale = $locale ?? app()->getLocale();
@endphp
@if ($fontLocale === 'en')
    <link rel="preload" href="{{ asset('fonts/source-sans-3-latin.woff2') }}" as="font" type="font/woff2" crossorigin>
@else
    <link rel="preload" href="{{ asset('fonts/cairo-arabic.woff2') }}" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="{{ asset('fonts/cairo-latin.woff2') }}" as="font" type="font/woff2" crossorigin>
@endif
<link rel="stylesheet" href="{{ asset('css/fonts.css') }}">
