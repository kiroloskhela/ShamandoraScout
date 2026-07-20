<!DOCTYPE html>
@php($locale = app()->getLocale())
<html lang="{{ $locale }}" dir="{{ $locale === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Problem occurred | Shamandora Scout') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&family=Source+Sans+3:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="icon" href="{{ asset('img/shamandora.webp') }}">
    <style>
        body { font-family: {{ $locale === 'ar' ? "'Tajawal'" : "'Source Sans 3'" }}, sans-serif; }
        .sea-bg {
            background:
                radial-gradient(ellipse 80% 50% at 50% -20%, rgba(245, 158, 11, 0.18), transparent),
                linear-gradient(165deg, #fffbeb 0%, #fef3c7 40%, #f8fafc 100%);
        }
    </style>
    @include('partials.motion-styles')
</head>
<body class="sea-bg min-h-screen flex flex-col">
    <main class="flex-1 flex items-center justify-center px-4 py-16">
        <div class="w-full max-w-lg text-center status-card-enter">
            <img src="{{ asset('img/shamandora.webp') }}" alt="{{ __('Shamandora') }}" class="mx-auto h-24 w-24 object-contain drop-shadow-md">
            <div class="mt-8 rounded-3xl bg-white/90 shadow-xl ring-1 ring-amber-100 px-8 py-10 backdrop-blur">
                <div class="mx-auto mb-5 flex h-14 w-14 items-center justify-center rounded-full bg-amber-50 text-amber-700 ring-1 ring-amber-100">
                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                    </svg>
                </div>
                <h1 class="text-2xl font-extrabold text-amber-900">{{ __('Sorry') }}</h1>
                <p class="mt-4 text-base leading-relaxed text-slate-700">{{ __('Go back to the previous page') }}</p>
                <p class="mt-2 text-base leading-relaxed text-indigo-700">{{ __('Make sure all required fields are filled in correctly') }}</p>
            </div>
            <p class="mt-8 text-xs text-slate-400">© {{ date('Y') }} {{ __('Shamandora Scout Group') }}</p>
        </div>
    </main>
</body>
</html>
