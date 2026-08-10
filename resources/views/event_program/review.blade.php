@extends('layouts.app', ['pageTitle' => __('Import review')])

@section('content')
    <div class="container mx-auto px-4 py-8 max-w-3xl">
        <h1 class="text-2xl font-bold mb-2">{{ __('Clarify import issues') }}</h1>
        <p class="text-sm text-gray-500 mb-6">
            {{ __('The assistant found ambiguities. Answer these questions, then we will commit the program.') }}
        </p>

        @if (session('error'))
            <div class="mb-4 p-3 rounded bg-red-50 text-red-700">{{ session('error') }}</div>
        @endif

        <form method="post" action="{{ route('event-program.import.answer', $session->id) }}" class="space-y-5" id="importReviewForm">
            @csrf
            @forelse ($session->questions_json ?? [] as $q)
                <div class="bg-white dark:bg-slate-800 rounded-xl shadow p-4">
                    <p class="font-medium mb-3">{{ $q['prompt'] }}</p>
                    @php $options = $q['options'] ?? []; @endphp
                    @if (!empty($options))
                        <div class="space-y-2">
                            @foreach ($options as $opt)
                                <label class="flex items-center gap-2 text-sm">
                                    <input type="radio" name="answers[{{ $q['id'] }}]" value="{{ $opt['value'] }}">
                                    <span>{{ $opt['label'] }}</span>
                                </label>
                            @endforeach
                        </div>
                    @else
                        <input type="text" name="answers[{{ $q['id'] }}]" class="w-full border rounded-lg px-3 py-2 dark:bg-slate-900">
                    @endif
                </div>
            @empty
                <p class="text-gray-500">{{ __('No questions — you can commit.') }}</p>
            @endforelse

            <div class="flex flex-wrap gap-2">
                <button type="submit" class="px-4 py-2 rounded-lg bg-emerald-600 text-white">{{ __('Apply answers & commit') }}</button>
                <button type="submit" name="skip_unresolved" value="1"
                    class="px-4 py-2 rounded-lg border border-amber-500 text-amber-700 dark:text-amber-300">
                    {{ __('Skip unanswered and import matched only') }}
                </button>
            </div>
        </form>
    </div>
@endsection
