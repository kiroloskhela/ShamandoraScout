@php
    $help = \App\Support\PageHelp::content();
    $isRtl = ($isRtl ?? app()->getLocale() === 'ar');
@endphp

<div class="relative" x-data="{ open: false }" @keydown.escape.window="open = false">
    <button type="button" @click="open = true"
        class="inline-flex h-10 w-10 items-center justify-center text-gray-600 dark:text-emerald-300/90 hover:text-gray-900 dark:hover:text-emerald-200 hover:bg-gray-100 dark:hover:bg-slate-800 rounded-lg transition-colors"
        title="{{ __('help.button') }}" aria-label="{{ __('help.button') }}">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z" />
        </svg>
    </button>

    <div x-show="open" x-cloak class="fixed inset-0 z-[60]" role="dialog" aria-modal="true"
        aria-labelledby="page-help-title">
        <div class="absolute inset-0 bg-black/40 dark:bg-black/60 backdrop-blur-[1px]" @click="open = false"></div>

        <div
            class="absolute inset-x-4 top-[12%] mx-auto max-w-lg overflow-hidden rounded-2xl bg-white dark:bg-slate-900 shadow-2xl ring-1 ring-gray-200 dark:ring-slate-700 status-card-enter sm:inset-x-auto sm:w-full {{ $isRtl ? 'sm:left-6' : 'sm:right-6' }}"
            @click.outside="open = false">
            <div
                class="flex items-start justify-between gap-3 border-b border-gray-200 dark:border-slate-700 px-5 py-4 bg-slate-50 dark:bg-slate-800/60">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700 dark:text-emerald-300">
                        {{ __('help.button') }}
                    </p>
                    <h2 id="page-help-title" class="mt-1 text-lg font-bold text-gray-900 dark:text-slate-100">
                        {{ $help['title'] }}
                    </h2>
                </div>
                <button type="button" @click="open = false"
                    class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-gray-500 hover:bg-gray-100 dark:hover:bg-slate-800 dark:text-slate-300"
                    aria-label="{{ __('help.close') }}">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="px-5 py-4 space-y-4 max-h-[60vh] overflow-y-auto">
                <p class="text-sm leading-relaxed text-gray-600 dark:text-slate-300">
                    {{ $help['intro'] }}
                </p>

                @if (!empty($help['steps']))
                    <ol class="space-y-2.5 {{ $isRtl ? 'list-decimal pr-5' : 'list-decimal pl-5' }} text-sm text-gray-700 dark:text-slate-200">
                        @foreach ($help['steps'] as $step)
                            <li class="leading-relaxed">{{ $step }}</li>
                        @endforeach
                    </ol>
                @endif
            </div>

            <div class="border-t border-gray-200 dark:border-slate-700 px-5 py-3 flex justify-end bg-white dark:bg-slate-900">
                <button type="button" @click="open = false"
                    class="inline-flex items-center rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-semibold px-4 py-2 transition-colors">
                    {{ __('help.close') }}
                </button>
            </div>
        </div>
    </div>
</div>
