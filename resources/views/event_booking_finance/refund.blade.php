@extends('layouts.app', ['pageTitle' => __('Full refund')])

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-2xl mx-auto border-2 border-red-300">
            <div class="mb-6 text-center">
                <h2 class="text-xl font-bold text-red-700">{{ __('Full refund') }}</h2>
            </div>

            <div class="mb-6 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-900">
                <div><strong>{{ __('Name:') }}</strong> {{ $booking->PersonFullName }}</div>
                <div><strong>{{ __('Event:') }}</strong> {{ $booking->EventTypeName }} - {{ $booking->EventName }}</div>
                <div><strong>{{ __('Total paid:') }}</strong> {{ number_format($booking->AmountPaid, 2) }}</div>
            </div>

            <div class="mb-6 text-center text-red-700 font-medium">{{ __('Are you sure you want to refund the full paid amount?') }}</div>

            <form method="POST"
                action="{{ route('eventBookingFinance.refundStore', $booking->SeasonEventParticipantFinanceID) }}">
                @csrf

                <div class="flex justify-center gap-3">
                    <a href="{{ route('eventBookingFinance.index', $booking->SeasonEventID) }}"
                        class="inline-flex items-center justify-center h-12 px-8 text-sm font-medium rounded-full bg-gray-100 text-gray-700 hover:bg-gray-200 transition">{{ __('Back') }}</a>

                    <button type="submit"
                        class="inline-flex items-center justify-center h-12 px-8 text-sm font-medium rounded-full bg-red-600 text-white hover:bg-red-700 transition">{{ __('Confirm refund') }}</button>
                </div>
            </form>
        </div>
    </div>
@endsection
