@props([
    'href' => '#',
    'title' => '',
    'count' => null, // hide the number if not passed
    'color' => 'blue', // tailwind color name
])

<a href="{{ $href }}"
    class="group block bg-white dark:bg-slate-900 rounded-xl shadow dark:shadow-[0_0_0_1px_rgba(51,65,85,0.8),0_12px_28px_rgba(0,0,0,0.35)] hover:shadow-xl focus:shadow-xl transition-all duration-300
          transform hover:scale-[1.02] ring-0 focus:outline-none focus-visible:ring-2 focus-visible:ring-{{ $color }}-400
          dark:hover:bg-slate-800/80"
    title="{{ $title }}">

    <div class="p-5 flex items-center gap-4">
        {{-- Fixed-size icon box so SVG never stretches --}}
        <div
            class="shrink-0 w-12 h-12 rounded-lg bg-{{ $color }}-50 dark:bg-{{ $color }}-950/50 text-{{ $color }}-600 dark:text-{{ $color }}-400
                    flex items-center justify-center
                    transition-transform duration-300 transform group-hover:scale-110 group-active:scale-95
                    dark:ring-1 dark:ring-{{ $color }}-500/20">

            @isset($icon)
                {{ $icon }}
            @endisset
        </div>

        <div class="flex-1">
            <p class="text-sm text-gray-600 dark:text-slate-400">{{ $title }}</p>
            @if (!is_null($count))
                <p class="mt-1 text-2xl font-semibold text-gray-900 dark:text-slate-50">{{ $count }}</p>
            @endif
        </div>

        {{-- subtle chevron --}}
        <svg class="w-5 h-5 text-gray-400 dark:text-slate-500 transition-transform group-hover:translate-x-0.5 dark:group-hover:text-emerald-400" viewBox="0 0 20 20"
            fill="currentColor" aria-hidden="true">
            <path fill-rule="evenodd"
                d="M7.293 14.707a1 1 0 010-1.414L9.586 11 7.293 8.707a1 1 0 111.414-1.414L12 10l-3.293 3.293a1 1 0 01-1.414 0z"
                clip-rule="evenodd" />
        </svg>
    </div>
</a>
