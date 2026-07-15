@extends('layouts.app', ['pageTitle' => __('Add payment')])

@section('content')
    <div class="container mx-auto px-4 py-8" dir="rtl">
        <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-4xl mx-auto border-2 border-blue-300">
            <div class="mb-6 text-center">
                <h2 class="text-xl font-bold text-gray-800">{{ __('Add new payment') }}</h2>
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

            <div class="mb-6 rounded-lg border border-blue-200 bg-blue-50 p-4 text-sm text-blue-900">
                <div><strong>{{ __('Name:') }}</strong> {{ $booking->PersonFullName }}</div>
                <div><strong>{{ __('Event:') }}</strong> {{ $booking->EventTypeName }} - {{ $booking->EventName }}</div>
                <div><strong>{{ __('Original price:') }}</strong> {{ number_format($booking->OriginalPrice, 2) }}</div>
                <div><strong>{{ __('Discount:') }}</strong> {{ number_format($booking->DiscountAmount, 2) }}</div>
                <div><strong>{{ __('Actual price after discount:') }}</strong> {{ number_format($booking->FinalRequiredAmount, 2) }}</div>
                <div><strong>{{ __('Final required:') }}</strong> {{ number_format($booking->FinalRequiredAmount, 2) }}</div>
                <div><strong>{{ __('Paid:') }}</strong> {{ number_format($booking->AmountPaid, 2) }}</div>
                <div><strong>{{ __('Remaining:') }}</strong> {{ number_format($booking->RemainingAmount, 2) }}</div>
                <div><strong>{{ __('Current installment:') }}</strong> {{ $nextInstallmentNumber }} {{ __('of') }} {{ $booking->InstallmentsNumber }}
                </div>
                <div><strong>{{ __('Payment date:') }}</strong> {{ now()->format('Y-m-d H:i') }}</div>

                @if ($isLastInstallment)
                    <div class="mt-2 text-red-700 font-bold">{{ __('This is the last payment and must equal the full remaining balance.') }}</div>
                @endif
            </div>

            @if ($previousPayments->count() > 0)
                <div class="mb-6 bg-white rounded-lg shadow border border-gray-200 overflow-x-auto">
                    <div class="px-4 py-3 bg-gray-50 border-b font-bold text-gray-800">{{ __('Previous payments') }}</div>

                    <table class="min-w-full text-sm text-right">
                        <thead class="bg-gray-100 text-gray-700">
                            <tr>
                                <th class="px-4 py-3">{{ __('Installment number') }}</th>
                                <th class="px-4 py-3">{{ __('Date') }}</th>
                                <th class="px-4 py-3">{{ __('Amount') }}</th>
                                <th class="px-4 py-3">{{ __('Receipt number') }}</th>
                                <th class="px-4 py-3">{{ __('Notes') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($previousPayments as $payment)
                                <tr class="border-t">
                                    <td class="px-4 py-3">{{ $payment->InstallmentNumber }}</td>
                                    <td class="px-4 py-3">{{ $payment->PaymentDate }}</td>
                                    <td class="px-4 py-3">{{ number_format($payment->Amount, 2) }}</td>
                                    <td class="px-4 py-3">{{ $payment->ReceiptNumber ?? '-' }}</td>
                                    <td class="px-4 py-3">{{ $payment->Notes ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            <form method="POST"
                action="{{ route('eventBookingFinance.storeInstallment', $booking->SeasonEventParticipantFinanceID) }}">
                @csrf

                <div class="space-y-6">
                    <div>
                        <label class="block mb-2 text-sm text-gray-700">{{ __('Amount') }}</label>

                        @if ($isLastInstallment)
                            <input type="number" step="0.01" min="0.01" name="amount" id="amount"
                                value="{{ number_format($booking->RemainingAmount, 2, '.', '') }}" readonly
                                class="w-full h-12 px-4 border rounded-lg text-right bg-gray-100 border-slate-200 text-slate-600">

                            <p class="text-xs text-red-600 mt-2">{{ __('This is the last payment, so the amount was set automatically to the full remaining balance.') }}</p>
                        @else
                            <input type="number" step="0.01" min="0.01" name="amount" id="amount"
                                value="{{ old('amount') }}"
                                class="w-full h-12 px-4 border rounded-lg text-right border-slate-200 text-slate-600">
                        @endif
                    </div>

                    <div>
                        <label class="block mb-2 text-sm text-gray-700">{{ __('Notes') }}</label>
                        <input type="text" name="notes" value="{{ old('notes') }}"
                            class="w-full h-12 px-4 border rounded-lg text-right border-slate-200 text-slate-600">
                    </div>

                    <div class="flex justify-center gap-3">
                        <a href="{{ route('eventBookingFinance.index', $booking->SeasonEventID) }}"
                            class="inline-flex items-center justify-center h-12 px-8 text-sm font-medium rounded-full bg-gray-100 text-gray-700 hover:bg-gray-200 transition">{{ __('Back') }}</a>

                        <button type="submit"
                            class="inline-flex items-center justify-center h-12 px-8 text-sm font-medium rounded-full bg-blue-50 text-blue-500 hover:bg-blue-100 hover:text-blue-600 transition">{{ __('Save and print receipt') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
