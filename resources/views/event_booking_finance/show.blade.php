@extends('layouts.app', ['pageTitle' => __('View booking details')])

@section('content')
    <div class="container mx-auto px-4 py-6" dir="rtl">
        @if (session('success'))
            <div class="mb-4 rounded-lg bg-green-100 border border-green-300 text-green-800 px-4 py-3">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-4 rounded-lg bg-red-100 border border-red-300 text-red-800 px-4 py-3">
                <ul class="space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="mb-4 bg-white rounded-2xl shadow-sm border border-slate-200 p-4">
            <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
                <div>
                    <h2 class="text-lg font-bold text-slate-800">{{ __('View booking details') }}</h2>
                    <div class="mt-2 flex flex-wrap gap-2 text-xs text-slate-600">
                        <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1">
                            {{ __('Season:') }} {{ $booking->SeasonName }} ({{ $booking->SeasonYear }})
                        </span>
                        <span class="inline-flex items-center rounded-full bg-blue-50 text-blue-700 px-3 py-1">
                            {{ $booking->EventName }}
                        </span>
                        <span class="inline-flex items-center rounded-full bg-violet-50 text-violet-700 px-3 py-1">
                            {{ $booking->PersonFullName }}
                        </span>
                    </div>
                </div>

                <div class="flex flex-wrap gap-2">
                    @if ((int) ($booking->IsRefunded ?? 0) === 0 && (int) ($booking->SendQrWhatsApp ?? 0) === 1)
                        <form method="POST" action="{{ route('eventBookingFinance.sendQr', $booking->SeasonEventParticipantFinanceID) }}">
                            @csrf
                            <button type="submit"
                                class="bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold py-2 px-4 rounded-lg transition-colors duration-200">
                                {{ __('Send QR code via WhatsApp') }}
                            </button>
                        </form>
                    @endif

                    @if ($canAddInstallment)
                        <a href="{{ route('eventBookingFinance.createInstallment', $booking->SeasonEventParticipantFinanceID) }}"
                            class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold py-2 px-4 rounded-lg transition-colors duration-200">
                            {{ __('Add payment') }}
                        </a>
                    @endif

                    @if ($canRefund)
                        <a href="{{ route('eventBookingFinance.refundPage', $booking->SeasonEventParticipantFinanceID) }}"
                            class="bg-red-600 hover:bg-red-700 text-white text-sm font-bold py-2 px-4 rounded-lg transition-colors duration-200">
                            {{ __('Full refund') }}
                        </a>

                        <a href="{{ route('eventBookingFinance.partialRefundPage', $booking->SeasonEventParticipantFinanceID) }}"
                            class="bg-orange-600 hover:bg-orange-700 text-white text-sm font-bold py-2 px-4 rounded-lg transition-colors duration-200">
                            {{ __('Partial refund with deduction') }}
                        </a>
                    @endif

                    <a href="{{ route('eventBookingFinance.index', $booking->SeasonEventID) }}"
                        class="bg-gray-100 hover:bg-gray-200 text-gray-800 text-sm font-bold py-2 px-4 rounded-lg transition-colors duration-200">{{ __('Back') }}</a>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-4 mb-4">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 xl:col-span-2">
                <h3 class="text-sm font-extrabold text-slate-800 mb-4">{{ __('Booking details') }}</h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-sm">
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                        <div class="text-slate-500 text-xs mb-1">{{ __('Name') }}</div>
                        <div class="font-bold text-slate-800">{{ $booking->PersonFullName }}</div>
                    </div>

                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                        <div class="text-slate-500 text-xs mb-1">{{ __('ID number') }}</div>
                        <div class="font-bold text-slate-800">{{ $booking->PersonID }}</div>
                    </div>

                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                        <div class="text-slate-500 text-xs mb-1">{{ __('Mobile') }}</div>
                        <div class="font-bold text-slate-800">{{ $booking->PersonPersonalMobileNumber ?: '-' }}</div>
                    </div>

                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                        <div class="text-slate-500 text-xs mb-1">{{ __('Original price') }}</div>
                        <div class="font-bold text-slate-800">{{ number_format($booking->OriginalPrice, 2) }}</div>
                    </div>

                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                        <div class="text-slate-500 text-xs mb-1">{{ __('Discount') }}</div>
                        <div class="font-bold text-slate-800">{{ number_format($booking->DiscountAmount, 2) }}</div>
                    </div>

                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                        <div class="text-slate-500 text-xs mb-1">{{ __('Final required') }}</div>
                        <div class="font-bold text-slate-800">{{ number_format($booking->FinalRequiredAmount, 2) }}</div>
                    </div>

                    <div class="rounded-xl border border-emerald-100 bg-emerald-50/70 p-3">
                        <div class="text-emerald-700 text-xs mb-1">{{ __('Paid') }}</div>
                        <div class="font-bold text-emerald-700">{{ number_format($booking->AmountPaid, 2) }}</div>
                    </div>

                    <div class="rounded-xl border border-red-100 bg-red-50/70 p-3">
                        <div class="text-red-700 text-xs mb-1">{{ __('Remaining') }}</div>
                        <div class="font-bold text-red-700">{{ number_format($booking->RemainingAmount, 2) }}</div>
                    </div>

                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                        <div class="text-slate-500 text-xs mb-1">{{ __('Installments count') }}</div>
                        <div class="font-bold text-slate-800">{{ $booking->InstallmentsNumber }}</div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4">
                <h3 class="text-sm font-extrabold text-slate-800 mb-4">{{ __('Update shirt size') }}</h3>

                <form method="POST"
                    action="{{ route('eventBookingFinance.updateShirtSize', $booking->SeasonEventParticipantFinanceID) }}"
                    class="space-y-3">
                    @csrf

                    <select name="shirt_size"
                        class="w-full h-11 px-4 border rounded-xl text-right border-slate-200 text-slate-700 focus:border-blue-500 focus:outline-none">
                        <option value="XS" {{ old('shirt_size', $booking->ShirtSize) == 'XS' ? 'selected' : '' }}>XS
                        </option>
                        <option value="S" {{ old('shirt_size', $booking->ShirtSize) == 'S' ? 'selected' : '' }}>S
                        </option>
                        <option value="M" {{ old('shirt_size', $booking->ShirtSize) == 'M' ? 'selected' : '' }}>M
                        </option>
                        <option value="L" {{ old('shirt_size', $booking->ShirtSize) == 'L' ? 'selected' : '' }}>L
                        </option>
                        <option value="XL" {{ old('shirt_size', $booking->ShirtSize) == 'XL' ? 'selected' : '' }}>XL
                        </option>
                        <option value="2XL" {{ old('shirt_size', $booking->ShirtSize) == '2XL' ? 'selected' : '' }}>2XL
                        </option>
                        <option value="3XL" {{ old('shirt_size', $booking->ShirtSize) == '3XL' ? 'selected' : '' }}>3XL
                        </option>
                        <option value="4XL" {{ old('shirt_size', $booking->ShirtSize) == '4XL' ? 'selected' : '' }}>4XL
                        </option>
                        <option value="5XL" {{ old('shirt_size', $booking->ShirtSize) == '5XL' ? 'selected' : '' }}>5XL
                        </option>
                        <option value="6XL" {{ old('shirt_size', $booking->ShirtSize) == '6XL' ? 'selected' : '' }}>6XL
                        </option>

                    </select>

                    <button type="submit"
                        class="w-full bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold py-2.5 px-4 rounded-xl transition-colors duration-200">{{ __('Update size') }}</button>
                </form>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-extrabold text-slate-800">{{ __('All payments details') }}</h3>
                <span class="text-xs text-slate-500">{{ __('A receipt can be printed for each payment') }}</span>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-right">
                    <thead class="bg-slate-50 text-slate-700">
                        <tr>
                            <th class="px-4 py-3">{{ __('Installment number') }}</th>
                            <th class="px-4 py-3">{{ __('Gender') }}</th>
                            <th class="px-4 py-3">{{ __('Amount') }}</th>
                            <th class="px-4 py-3">{{ __('Date') }}</th>
                            <th class="px-4 py-3">{{ __('Servant') }}</th>
                            <th class="px-4 py-3">{{ __('Notes') }}</th>
                            <th class="px-4 py-3">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $payment)
                            <tr class="border-t border-slate-100">
                                <td class="px-4 py-3">{{ $payment->InstallmentNumber }}</td>
                                <td class="px-4 py-3">
                                    @if ($payment->PaymentType === 'PAYMENT')
                                        <span
                                            class="inline-flex px-2 py-1 rounded-full text-xs bg-emerald-100 text-emerald-700">{{ __('Payment') }}</span>
                                    @else
                                        <span class="inline-flex px-2 py-1 rounded-full text-xs bg-red-100 text-red-700">{{ __('Refunded') }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 font-bold">{{ $payment->AmountFormatted }}</td>
                                <td class="px-4 py-3">{{ $payment->PaymentDateFormatted }}</td>
                                <td class="px-4 py-3">{{ $payment->ServentFullName ?: '-' }}</td>
                                <td class="px-4 py-3">{{ $payment->Notes ?: '-' }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex flex-wrap gap-2">
                                        <a href="{{ route('eventBookingFinance.printReceipt', $payment->PaymentID) }}"
                                            class="inline-flex items-center px-3 py-2 text-xs font-medium rounded-md text-white bg-gray-600 hover:bg-gray-700 transition-colors duration-200">
                                            {{ __('Print receipt') }}
                                        </a>

                                        @if ($payment->PaymentID == optional($payments->last())->PaymentID && $payment->PaymentType === 'PAYMENT')
                                            <a href="{{ route('eventBookingFinance.editLastPayment', $payment->PaymentID) }}"
                                                class="inline-flex items-center px-3 py-2 text-xs font-medium rounded-md text-white bg-green-600 hover:bg-green-700 transition-colors duration-200">
                                                {{ __('Edit last payment') }}
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-6 text-center text-slate-500">{{ __('No payments recorded.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
