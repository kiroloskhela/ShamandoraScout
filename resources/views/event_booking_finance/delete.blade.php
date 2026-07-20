@extends('layouts.app', ['pageTitle' => __('Confirm delete booking')])

@section('content')
    <div class="flex place-content-center" dir="rtl">
        <div class="bg-white dark:bg-slate-900 rounded-lg shadow-lg p-8 w-full max-w-2xl border-2 border-red-300 dark:border-red-800">
            <div class="mb-6 text-center">
                <h2 class="text-xl font-bold text-red-700 dark:text-red-300">{{ __('Confirm delete booking') }}</h2>
                <p class="mt-2 text-sm text-red-600 dark:text-red-400 font-semibold">{{ __('This action cannot be undone.') }}</p>
            </div>

            @if ($errors->has('general'))
                <div class="mb-4 rounded-lg bg-red-100 border border-red-300 text-red-800 px-4 py-3">
                    {{ $errors->first('general') }}
                </div>
            @endif

            <div class="mb-6 rounded-lg border border-red-200 dark:border-red-900 bg-red-50 dark:bg-red-950/40 p-4 text-sm text-red-900 dark:text-red-200">
                {{ __('Are you sure you want to permanently delete this booking? All payments and receipts for this booking will be deleted.') }}
            </div>

            <div class="mb-6 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950 p-6 space-y-3 text-sm text-gray-800 dark:text-slate-200">
                <div><strong>{{ __('Season:') }}</strong> {{ $booking->SeasonName }} ({{ $booking->SeasonYear }})</div>
                <div><strong>{{ __('Event:') }}</strong> {{ $booking->EventTypeName }} - {{ $booking->EventName }}</div>
                <hr class="border-slate-200 dark:border-slate-700">
                <div><strong>{{ __('Name:') }}</strong> {{ $booking->PersonFullName }}</div>
                <div><strong>{{ __('Code') }}:</strong> {{ $booking->BookingCode }}</div>
                <div><strong>{{ __('Mobile:') }}</strong> {{ $booking->PersonPersonalMobileNumber ?? '-' }}</div>
                <div><strong>{{ __('Type') }}:</strong> {{ $booking->BookingEntityLabel }}</div>
                <div><strong>{{ __('Payments to delete') }}:</strong> {{ $paymentsCount }}</div>
                <div><strong>{{ __('Receipts to delete') }}:</strong> {{ $receiptsCount }}</div>
            </div>

            <div class="flex justify-center gap-3">
                <a href="{{ route('eventBookingFinance.show', $booking->SeasonEventParticipantFinanceID) }}"
                    class="inline-flex items-center justify-center h-12 px-8 text-sm font-medium rounded-full bg-gray-100 dark:bg-slate-800 text-gray-700 dark:text-slate-200 hover:bg-gray-200 dark:hover:bg-slate-700 transition">{{ __('Back') }}</a>

                <form method="POST" action="{{ route('eventBookingFinance.destroy', $booking->SeasonEventParticipantFinanceID) }}">
                    @csrf
                    @method('DELETE')

                    <button type="submit"
                        class="inline-flex items-center justify-center h-12 px-8 text-sm font-medium rounded-full bg-red-600 text-white hover:bg-red-700 transition">
                        {{ __('Confirm permanent delete') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection
