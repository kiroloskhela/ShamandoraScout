{{-- Shared SEO / Open Graph / Organization schema for Google, social, and answer engines --}}
@php
    $seoTitle = $seoTitle ?? __('Shamandora Scout');
    $seoDescription = $seoDescription
        ?? __('Egyptian Sea Scout group. Official Shamandora Scout portal for activities, events, registration, and news.');
    $appBase = rtrim((string) config('app.url'), '/');
    $seoUrl = $seoUrl ?? url()->current();
    $seoCanonical = $seoCanonical ?? $seoUrl;
    $seoImage = $seoImage ?? asset('img/og-image.png');
    $seoLogo = $seoLogo ?? asset('img/logo-square.png');
    $seoSiteName = config('seo.site_name', 'Shamandora Scout');
    $seoLocale = app()->getLocale() === 'ar' ? 'ar_EG' : 'en_US';
    $allowedRobots = ['index, follow', 'index,follow', 'noindex, nofollow', 'noindex,nofollow'];
    $seoRobotsRaw = $seoRobots ?? 'index, follow';
    $seoRobots = in_array($seoRobotsRaw, $allowedRobots, true) ? $seoRobotsRaw : 'index, follow';
    $sameAs = array_values(config('seo.same_as', []));
    $organizationId = $appBase.'/#organization';
    $websiteId = $appBase.'/#website';
    $jsonFlags = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_PRETTY_PRINT;
@endphp

<title>{{ $seoTitle }}</title>
<meta name="description" content="{{ $seoDescription }}">
<meta name="keywords"
    content="الشمندوره البحريه, Shamandora Scout, Shamandora Sea Scout, scouts, sea scout, shamandora, الكشافة, الكشفية البحرية, كشافة الشمندورة">
<meta name="robots" content="{{ $seoRobots }}">
<meta name="author" content="{{ $seoSiteName }}">
<meta name="theme-color" content="#0b1220">
<link rel="canonical" href="{{ $seoCanonical }}">

{{-- Favicon / browser & Google search icon (this brand logo) --}}
<link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
<link rel="icon" type="image/png" href="{{ asset('favicon.png') }}" sizes="48x48">
<link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32.png') }}">
<link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16.png') }}">
<link rel="icon" type="image/png" sizes="192x192" href="{{ asset('favicon-192.png') }}">
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

{{-- Organization (entity graph for search + answer engines) --}}
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => ['Organization', 'SportsOrganization'],
    '@id' => $organizationId,
    'name' => config('seo.site_name'),
    'legalName' => config('seo.legal_name'),
    'alternateName' => config('seo.alternate_names'),
    'description' => 'Egyptian Sea Scout group providing scouting activities, camps, training, and community programs. Official Shamandora Scout website.',
    'url' => $appBase.'/',
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
    'sameAs' => $sameAs,
], $jsonFlags) !!}
</script>

{{-- WebSite node linked to Organization --}}
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'WebSite',
    '@id' => $websiteId,
    'name' => config('seo.site_name'),
    'url' => $appBase.'/',
    'description' => 'Official website of Shamandora Scout (الشمندوره البحريه), an Egyptian Sea Scout group.',
    'inLanguage' => [app()->getLocale() === 'ar' ? 'ar' : 'en', 'ar', 'en'],
    'publisher' => [
        '@id' => $organizationId,
    ],
], $jsonFlags) !!}
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
    'url' => $appBase.'/',
    'image' => $seoLogo,
    'author' => [
        '@id' => $organizationId,
    ],
    'installUrl' => [
        config('seo.same_as.play_store'),
        config('seo.same_as.app_store'),
    ],
    'offers' => [
        '@type' => 'Offer',
        'price' => '0',
        'priceCurrency' => 'USD',
    ],
], $jsonFlags) !!}
</script>
