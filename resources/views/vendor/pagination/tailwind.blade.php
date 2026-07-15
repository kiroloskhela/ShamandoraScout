@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}"
        class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between rounded-xl border border-gray-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-4 py-3 shadow-sm">

        <p class="text-sm text-gray-600 dark:text-slate-400 order-2 sm:order-1 text-center sm:text-start">
            {{ __('Showing') }}
            @if ($paginator->firstItem())
                <span class="font-semibold text-gray-900 dark:text-slate-100">{{ $paginator->firstItem() }}</span>
                {{ __('to') }}
                <span class="font-semibold text-gray-900 dark:text-slate-100">{{ $paginator->lastItem() }}</span>
            @else
                <span class="font-semibold text-gray-900 dark:text-slate-100">{{ $paginator->count() }}</span>
            @endif
            {{ __('of') }}
            <span class="font-semibold text-gray-900 dark:text-slate-100">{{ $paginator->total() }}</span>
            {{ __('results') }}
        </p>

        <div class="order-1 sm:order-2 flex items-center justify-center gap-1" dir="ltr">
            {{-- Previous --}}
            @if ($paginator->onFirstPage())
                <span aria-disabled="true"
                    class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200 dark:border-slate-700 text-gray-300 dark:text-slate-600 cursor-not-allowed"
                    aria-label="{{ __('Previous') }}">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                    </svg>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev"
                    class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200 dark:border-slate-600 bg-white dark:bg-slate-800 text-gray-700 dark:text-slate-200 hover:bg-emerald-50 dark:hover:bg-emerald-950/40 hover:border-emerald-300 dark:hover:border-emerald-700 hover:text-emerald-700 dark:hover:text-emerald-300 transition-colors"
                    aria-label="{{ __('Previous') }}">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
            @endif

            {{-- Pages --}}
            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="inline-flex h-9 min-w-9 items-center justify-center px-1 text-sm text-gray-400 dark:text-slate-500" aria-hidden="true">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span aria-current="page"
                                class="inline-flex h-9 min-w-9 items-center justify-center rounded-lg bg-emerald-600 px-3 text-sm font-bold text-white shadow-sm shadow-emerald-600/30">
                                {{ $page }}
                            </span>
                        @else
                            <a href="{{ $url }}"
                                class="inline-flex h-9 min-w-9 items-center justify-center rounded-lg border border-gray-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 text-sm font-medium text-gray-700 dark:text-slate-200 hover:bg-emerald-50 dark:hover:bg-emerald-950/40 hover:border-emerald-300 dark:hover:border-emerald-700 hover:text-emerald-700 dark:hover:text-emerald-300 transition-colors"
                                aria-label="{{ __('Go to page :page', ['page' => $page]) }}">
                                {{ $page }}
                            </a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next"
                    class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200 dark:border-slate-600 bg-white dark:bg-slate-800 text-gray-700 dark:text-slate-200 hover:bg-emerald-50 dark:hover:bg-emerald-950/40 hover:border-emerald-300 dark:hover:border-emerald-700 hover:text-emerald-700 dark:hover:text-emerald-300 transition-colors"
                    aria-label="{{ __('Next') }}">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            @else
                <span aria-disabled="true"
                    class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200 dark:border-slate-700 text-gray-300 dark:text-slate-600 cursor-not-allowed"
                    aria-label="{{ __('Next') }}">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                    </svg>
                </span>
            @endif
        </div>
    </nav>
@endif
