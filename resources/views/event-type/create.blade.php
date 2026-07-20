@extends('layouts.app', ['pageTitle' => __('Add event type')])

@section('content')
    <div class="flex place-content-center">
        <div class="bg-white dark:bg-slate-900 rounded-lg shadow-lg p-8 w-full max-w-md border-2 border-blue-300 dark:border-slate-700">
            <div class="mb-6 text-center">
                <h2 class="text-xl font-bold text-gray-800 dark:text-slate-100">{{ __('Add event type') }}</h2>
            </div>

            <form method="POST" action="{{ route('event-type.insert') }}" class="space-y-6">
                @csrf

                <div class="relative">
                    <label for="event_type_name" class="block mb-2 text-sm font-semibold text-gray-700 dark:text-slate-200">{{ __('Event type name') }}</label>
                    <input id="event_type_name" type="text" name="event_type_name" value="{{ old('event_type_name') }}" required
                        class="w-full h-12 ps-4 border rounded-lg border-slate-200 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 focus:border-blue-500 focus:outline-none"
                        placeholder="{{ __('Enter event type name') }}">
                    @error('event_type_name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="takes_reservation" value="1" {{ old('takes_reservation') ? 'checked' : '' }}
                        class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                    <span class="text-sm font-semibold text-gray-700 dark:text-slate-200">{{ __('Takes reservation') }}</span>
                </label>
                <p class="text-xs text-slate-500 dark:text-slate-400 -mt-4">{{ __('Reservation events use booking finance and entity QR attendance.') }}</p>

                <div class="flex justify-center">
                    <button type="submit"
                        class="inline-flex items-center justify-center h-12 px-8 text-sm font-medium rounded-full bg-blue-50 text-blue-600 hover:bg-blue-100 transition">
                        {{ __('Add event type') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
