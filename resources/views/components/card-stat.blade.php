@props([
    'href' => '#',
    'title' => '',
    'count' => null, // hide the number if not passed
    'color' => 'blue', // tailwind color name
    'compact' => false,
])

<a href="{{ $href }}"
    @class([
        'group block bg-white dark:bg-slate-900 rounded-xl shadow dark:shadow-[0_0_0_1px_rgba(51,65,85,0.8),0_12px_28px_rgba(0,0,0,0.35)] hover:shadow-xl focus:shadow-xl transition-all duration-300 transform hover:scale-[1.02] ring-0 focus:outline-none focus-visible:ring-2 dark:hover:bg-slate-800/80 border border-transparent dark:border-slate-800',
        'focus-visible:ring-blue-400' => $color === 'blue',
        'focus-visible:ring-emerald-400' => $color === 'emerald',
        'focus-visible:ring-indigo-400' => $color === 'indigo',
        'focus-visible:ring-yellow-400' => $color === 'yellow',
        'focus-visible:ring-pink-400' => $color === 'pink',
        'focus-visible:ring-rose-400' => $color === 'rose',
    ])
    title="{{ $title }}">

    <div @class([
        'flex items-center',
        'p-4 gap-3' => $compact,
        'p-5 gap-4' => ! $compact,
    ])>
        <div @class([
            'shrink-0 rounded-lg flex items-center justify-center transition-transform duration-300 transform group-hover:scale-110 group-active:scale-95 dark:ring-1',
            'w-10 h-10' => $compact,
            'w-12 h-12' => ! $compact,
            'bg-blue-50 text-blue-600 dark:bg-blue-950/50 dark:text-blue-400 dark:ring-blue-500/20' => $color === 'blue',
            'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/50 dark:text-emerald-400 dark:ring-emerald-500/20' => $color === 'emerald',
            'bg-indigo-50 text-indigo-600 dark:bg-indigo-950/50 dark:text-indigo-400 dark:ring-indigo-500/20' => $color === 'indigo',
            'bg-yellow-50 text-yellow-600 dark:bg-yellow-950/50 dark:text-yellow-400 dark:ring-yellow-500/20' => $color === 'yellow',
            'bg-pink-50 text-pink-600 dark:bg-pink-950/50 dark:text-pink-400 dark:ring-pink-500/20' => $color === 'pink',
            'bg-rose-50 text-rose-600 dark:bg-rose-950/50 dark:text-rose-400 dark:ring-rose-500/20' => $color === 'rose',
        ])>
            @isset($icon)
                {{ $icon }}
            @endisset
        </div>

        <div class="flex-1 min-w-0">
            <p @class([
                'text-gray-600 dark:text-slate-400 leading-snug',
                'text-xs font-semibold' => $compact,
                'text-sm' => ! $compact,
            ])>{{ $title }}</p>
            @if (! is_null($count))
                <p @class([
                    'mt-1 font-semibold text-gray-900 dark:text-slate-50',
                    'text-xl' => $compact,
                    'text-2xl' => ! $compact,
                ])>{{ $count }}</p>
            @endif
        </div>

        <svg class="w-5 h-5 shrink-0 text-gray-400 dark:text-slate-500 transition-transform group-hover:translate-x-0.5 rtl:rotate-180 rtl:group-hover:translate-x-0 rtl:group-hover:-translate-x-0.5 dark:group-hover:text-emerald-400"
            viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
            <path fill-rule="evenodd"
                d="M7.293 14.707a1 1 0 010-1.414L9.586 11 7.293 8.707a1 1 0 111.414-1.414L12 10l-3.293 3.293a1 1 0 01-1.414 0z"
                clip-rule="evenodd" />
        </svg>
    </div>
</a>
