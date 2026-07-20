@extends('layouts.app', ['pageTitle' => __('Partial refund with deduction')])

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-3xl mx-auto bg-white rounded-lg shadow border border-slate-200">
            <div class="px-6 py-4 border-b border-slate-200">
                <h2 class="text-xl font-bold text-slate-800">{{ __('Refund amount with partial deduction') }}</h2>
                <p class="text-sm text-slate-500 mt-1">{{ __('Specify the amount to deduct; the remaining paid amount will be refunded to the member.') }}</p>
            </div>

            <div class="p-6">
                @if ($errors->any())
                    <div class="mb-6 rounded-lg bg-red-100 border border-red-300 text-red-800 px-4 py-3">
                        <ul class="list-disc pr-5 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if (session('success'))
                    <div class="mb-6 rounded-lg bg-green-100 border border-green-300 text-green-800 px-4 py-3">
                        {{ session('success') }}
                    </div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div class="bg-slate-50 rounded-lg p-4 border border-slate-200">
                        <div class="text-sm text-slate-500 mb-1">{{ __('Member name') }}</div>
                        <div class="font-bold text-slate-800">{{ $booking->PersonFullName }}</div>
                    </div>

                    <div class="bg-slate-50 rounded-lg p-4 border border-slate-200">
                        <div class="text-sm text-slate-500 mb-1">{{ __('Paid amount') }}</div>
                        <div class="font-bold text-green-700">{{ number_format($booking->AmountPaid, 2) }}</div>
                    </div>
                </div>

                <form
                    action="{{ route('eventBookingFinance.partialRefundStore', $booking->SeasonEventParticipantFinanceID) }}"
                    method="POST" class="space-y-6">
                    @csrf

                    <div>
                        <label for="deduction_amount" class="block mb-2 text-sm text-gray-700">{{ __('Deducted portion') }}</label>
                        <input type="number" step="1" min="0" name="deduction_amount" id="deduction_amount"
                            value="{{ old('deduction_amount') }}"
                            class="w-full h-12 ps-4 border rounded-lg border-slate-200 text-slate-600 focus:border-orange-500 focus:outline-none"
                            placeholder="{{ __('Enter the portion to deduct from the paid amount') }}">
                        <p class="text-xs text-slate-500 mt-2">{{ __('Example: if paid is 1000 and you enter 200, 800 will be refunded and 200 kept.') }}</p>
                    </div>

                    <div>
                        <label for="notes" class="block mb-2 text-sm text-gray-700">{{ __('Notes') }}</label>
                        <textarea name="notes" id="notes" rows="4"
                            class="w-full px-4 py-3 border rounded-lg text-right border-slate-200 text-slate-600 focus:border-orange-500 focus:outline-none"
                            placeholder="{{ __('Additional notes if any') }}">{{ old('notes') }}</textarea>
                    </div>

                    <div class="flex gap-3">
                        <button type="submit"
                            class="bg-orange-600 hover:bg-orange-700 text-white font-bold py-2 px-6 rounded-lg transition-colors duration-200">{{ __('Process refund') }}</button>

                        <a href="{{ route('eventBookingFinance.index', $booking->SeasonEventID) }}"
                            class="bg-gray-100 hover:bg-gray-200 text-gray-800 font-bold py-2 px-6 rounded-lg transition-colors duration-200">{{ __('Back') }}</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
