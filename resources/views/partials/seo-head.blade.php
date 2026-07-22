{{-- Shared SEO / Open Graph / Organization schema for Google & social previews --}}
@php
    $seoTitle = $seoTitle ?? __('Shamandora Scout');
    $seoDescription = $seoDescription
        ?? __('Egyptian Sea Scout group. Official Shamandora Scout portal for activities, events, registration, and news.');
    $seoUrl = $seoUrl ?? url()->current();
    $seoCanonical = $seoCanonical ?? $seoUrl;
    $seoImage = $seoImage ?? asset('img/og-image.png');
    $seoLogo = $seoLogo ?? asset('img/logo-square.png');
    $seoSiteName = 'Shamandora Scout';
    $seoLocale = app()->getLocale() === 'ar' ? 'ar_EG' : 'en_US';
@endphp

<title>{{ $seoTitle }}</title>
<meta name="description" content="{{ $seoDescription }}">
<meta name="keywords"
    content="الشمندوره البحريه, Shamandora Scout, Shamandora Sea Scout, scouts, sea scout, shamandora, الكشافة, الكشفية البحرية, كشافة الشمندورة">
<meta name="robots" content="index, follow">
<meta name="author" content="Shamandora Scout">
<meta name="theme-color" content="#0b1220">
<link rel="canonical" href="{{ $seoCanonical }}">

{{-- Favicon / browser & Google search icon --}}
<link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
<link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32.png') }}">
<link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16.png') }}">
<link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">

{{-- Open Graph --}}
<meta property="og:type" content="website">
<meta property="og:locale" content="{{ $seoLocale }}">
<meta property="og:title" content="{{ $seoTitle }}">
<meta property="og:description" content="{{ $seoDescription }}">
<meta property="og:url" content="{{ $seoUrl }}">
<meta property="og:site_name" content="{{ $seoSiteName }}">
<meta property="og:image" content="{{ $seoImage }}">
<meta property="og:image:secure_url" content="{{ $seoImage }}">
<meta property="og:image:type" content="image/png">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:image:alt" content="{{ $seoSiteName }}">

{{-- Twitter / X --}}
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $seoTitle }}">
<meta name="twitter:description" content="{{ $seoDescription }}">
<meta name="twitter:image" content="{{ $seoImage }}">
<meta name="twitter:image:alt" content="{{ $seoSiteName }}">

{{-- Organization structured data (helps Google show logo & correct entity type) --}}
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => ['Organization', 'SportsOrganization'],
    'name' => 'Shamandora Scout',
    'legalName' => 'Shamandora Sea Scout',
    'alternateName' => ['الشمندوره البحريه', 'كشافة الشمندورة', 'Shamandora Sea Scout', 'ShamandoraScout'],
    'description' => 'Egyptian Sea Scout group providing scouting activities, camps, training, and community programs.',
    'url' => url('/'),
    'logo' => [
        '@type' => 'ImageObject',
        'url' => $seoLogo,
        'contentUrl' => $seoLogo,
        'width' => 512,
        'height' => 512,
        'caption' => 'Shamandora Scout logo',
    ],
    'image' => $seoImage,
    'foundingLocation' => [
        '@type' => 'Place',
        'address' => [
            '@type' => 'PostalAddress',
            'addressCountry' => 'EG',
        ],
    ],
    'sameAs' => [
        'https://www.facebook.com/ShamandoraScout',
        'https://www.instagram.com/shamandora_scout',
        'https://www.youtube.com/channel/UCn-U_L8wo8AMCFVesH-D6SA',
        'https://open.spotify.com/artist/6UxngCQeJnijih2mXIhb7Z',
        'https://play.anghami.com/artist/16097225',
        'https://apps.apple.com/us/app/shamandora/id6760709448',
        'https://play.google.com/store/apps/details?id=com.shamandora.shamandora',
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
</script>

{{-- Mobile apps (App Store + Google Play) --}}
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'MobileApplication',
    'name' => 'Shamandora',
    'operatingSystem' => 'ANDROID, IOS',
    'applicationCategory' => 'LifestyleApplication',
    'description' => 'Official Shamandora Scout mobile app.',
    'url' => url('/'),
    'image' => $seoLogo,
    'author' => [
        '@type' => 'Organization',
        'name' => 'Shamandora Scout',
        'url' => url('/'),
    ],
    'installUrl' => [
        'https://play.google.com/store/apps/details?id=com.shamandora.shamandora',
        'https://apps.apple.com/us/app/shamandora/id6760709448',
    ],
    'offers' => [
        '@type' => 'Offer',
        'price' => '0',
        'priceCurrency' => 'USD',
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
</script>
