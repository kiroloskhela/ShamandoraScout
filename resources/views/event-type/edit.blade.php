@extends('layouts.app', ['pageTitle' => __('Edit event type')])

@section('content')
    <div class="flex place-content-center">
        <div class="bg-white dark:bg-slate-900 rounded-lg shadow-lg p-8 w-full max-w-md border-2 border-emerald-300 dark:border-slate-700">
            <div class="mb-6 text-center">
                <h2 class="text-xl font-bold text-gray-800 dark:text-slate-100">{{ __('Edit event type') }}</h2>
            </div>

            <form method="POST" action="{{ route('event-type.update', $eventType->EventTypeID) }}" class="space-y-6">
                @csrf
                @method('PATCH')

                <div class="relative">
                    <label for="event_type_name" class="block mb-2 text-sm font-semibold text-gray-700 dark:text-slate-200">{{ __('Event type name') }}</label>
                    <input id="event_type_name" type="text" name="event_type_name"
                        value="{{ old('event_type_name', $eventType->EventTypeName) }}" required
                        class="w-full h-12 ps-4 border rounded-lg border-slate-200 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 focus:border-emerald-500 focus:outline-none">
                    @error('event_type_name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="takes_reservation" value="1"
                        {{ old('takes_reservation', $eventType->TakesReservation ?? 0) ? 'checked' : '' }}
                        class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                    <span class="text-sm font-semibold text-gray-700 dark:text-slate-200">{{ __('Takes reservation') }}</span>
                </label>
                <p class="text-xs text-slate-500 dark:text-slate-400 -mt-4">{{ __('Reservation events use booking finance and entity QR attendance.') }}</p>

                <div class="flex justify-center">
                    <button type="submit"
                        class="inline-flex items-center justify-center h-12 px-8 text-sm font-medium rounded-full bg-emerald-50 text-emerald-600 hover:bg-emerald-100 transition">
                        {{ __('Edit') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
