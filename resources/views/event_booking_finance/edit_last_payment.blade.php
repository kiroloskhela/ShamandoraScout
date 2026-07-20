@extends('layouts.app', ['pageTitle' => __('Edit last payment')])

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-2xl mx-auto border-2 border-green-300">
            <div class="mb-6 text-center">
                <h2 class="text-xl font-bold text-gray-800">{{ __('Edit last payment') }}</h2>
            </div>

            @if ($errors->any())
                <div class="mb-6 rounded-lg bg-red-100 border border-red-300 text-red-800 px-4 py-3">
                    <ul class="list-disc pr-5 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="mb-6 rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-900">
                <div><strong>PaymentID:</strong> {{ $payment->PaymentID }}</div>
                <div><strong>{{ __('Installment number:') }}</strong> {{ $payment->InstallmentNumber }} {{ __('of') }} {{ $payment->InstallmentsNumber }}
                </div>
                <div><strong>{{ __('Current amount:') }}</strong> {{ number_format($payment->Amount, 2) }}</div>
            </div>

            <form method="POST" action="{{ route('eventBookingFinance.updateLastPayment', $payment->PaymentID) }}">
                @csrf

                <div class="space-y-6">
                    <div>
                        <label class="block mb-2 text-sm text-gray-700">{{ __('New amount') }}</label>
                        <input type="number" step="1" min="0" name="amount"
                            value="{{ old('amount', $payment->Amount) }}"
                            class="w-full h-12 ps-4 border rounded-lg border-slate-200 text-slate-600">
                    </div>

                    <div class="flex justify-center gap-3">
                        <a href="{{ route('eventBookingFinance.index', $payment->SeasonEventID) }}"
                            class="inline-flex items-center justify-center h-12 px-8 text-sm font-medium rounded-full bg-gray-100 text-gray-700 hover:bg-gray-200 transition">{{ __('Back') }}</a>

                        <button type="submit"
                            class="inline-flex items-center justify-center h-12 px-8 text-sm font-medium rounded-full bg-green-50 text-green-600 hover:bg-green-100 transition">{{ __('Save and print receipt') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
