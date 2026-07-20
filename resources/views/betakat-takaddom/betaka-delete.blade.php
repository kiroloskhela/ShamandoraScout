@extends('layouts.app', ['pageTitle' => __('Progress badge certificate')])

@section('content')
    <div class="flex place-content-center">
        <div class="bg-white dark:bg-slate-900 rounded-lg shadow-lg p-8 w-full max-w-md border-2 border-red-300 dark:border-slate-700">
            <div class="mb-6 text-center">
                <h2 class="text-xl font-bold text-gray-800 dark:text-slate-100">{{ __('Delete progress badge certificate') }}</h2>
                <p class="mt-3 text-sm text-gray-600 dark:text-slate-300">
                    {{ __('Are you sure you want to delete progress badge certificate :name?', ['name' => $betakat->EgazetBetakatTaqaddomName]) }}
                </p>
            </div>

            <form method="POST" action="{{ route('betaka.destroy', $betakat->EgazetBetakatTaqaddomID) }}" class="space-y-4">
                @csrf
                @method('DELETE')

                <div class="flex flex-col gap-3">
                    <button type="submit"
                        class="inline-flex items-center justify-center h-12 px-8 text-sm font-medium tracking-wide transition duration-300 rounded-full bg-red-50 text-red-600 hover:bg-red-100 hover:text-red-700">
                        {{ __('Delete') }}
                    </button>
                    <a href="{{ route('betaka.index') }}"
                        class="inline-flex items-center justify-center h-12 px-8 text-sm font-medium tracking-wide transition duration-300 rounded-full bg-indigo-50 text-indigo-600 hover:bg-indigo-100 hover:text-indigo-700">
                        {{ __('Back') }}
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection
