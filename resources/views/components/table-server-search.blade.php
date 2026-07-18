@props([
    'action' => null,
    'placeholder' => null,
    'q' => null,
])

@php
    $action = $action ?: url()->current();
    $placeholder = $placeholder ?: __('Name / code / phone / ID');
    $q = $q ?? request('q');
    $hidden = request()->except(['q', 'page']);
@endphp

<form method="GET" action="{{ $action }}"
    {{ $attributes->merge(['class' => 'mb-4 flex flex-wrap gap-2 items-end']) }}>
    @foreach ($hidden as $key => $value)
        @if (! is_array($value) && $value !== null && $value !== '')
            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
        @endif
    @endforeach
    <div class="flex-1 min-w-[220px]">
        <label class="block text-sm text-gray-600 dark:text-slate-300 mb-1">{{ __('Search') }}</label>
        <input type="text" name="q" value="{{ $q }}" placeholder="{{ $placeholder }}"
            class="w-full border border-gray-300 dark:border-slate-600 rounded-lg px-3 py-2 bg-gray-50 dark:bg-slate-800 dark:text-slate-100">
    </div>
    <button type="submit"
        class="bg-gray-800 dark:bg-slate-200 text-white dark:text-slate-900 px-4 py-2 rounded-lg font-semibold">
        {{ __('Apply filter') }}
    </button>
    <a href="{{ $action }}"
        class="bg-gray-200 dark:bg-slate-700 text-gray-800 dark:text-slate-100 px-4 py-2 rounded-lg">
        {{ __('Reset') }}
    </a>
</form>
