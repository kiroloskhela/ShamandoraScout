@extends('layouts.app', ['pageTitle' => __('Edit finance plan')])

@section('content')
    <div class="flex place-content-center">
        <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-5xl border-2 border-green-300">
            <div class="mb-6 text-center">
                <h2 class="text-xl font-bold text-gray-800">{{ __('Edit finance plan') }}</h2>
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
                <div><strong>{{ __('Season:') }}</strong> {{ $finance->SeasonName }} ({{ $finance->SeasonYear }})</div>
                <div><strong>{{ __('Event:') }}</strong> {{ $finance->EventName }}</div>
                <div><strong>{{ __('Event start:') }}</strong> {{ $finance->EventStartDate }}</div>
                <div><strong>{{ __('Event end:') }}</strong> {{ $finance->EventEndDate }}</div>
            </div>

            <form method="POST" action="{{ route('finance.update', $finance->SeasonEventID) }}">
                @csrf
                <div class="space-y-6">

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <div>
                            <label for="max_installments_number" class="block mb-2 text-sm text-gray-700">{{ __('Max installments') }}</label>
                            <input type="number" min="1" id="max_installments_number" name="max_installments_number"
                                value="{{ old('max_installments_number', (int) $finance->MaxInstallmentsNumber) }}"
                                class="w-full h-12 ps-4 border rounded-lg border-slate-200 text-slate-600 focus:border-blue-500 focus:outline-none">
                        </div>

                        <div>
                            <label for="minimum_deposit" class="block mb-2 text-sm text-gray-700">{{ __('Minimum deposit') }}</label>
                            <input type="number" step="1" min="0" id="minimum_deposit" name="minimum_deposit"
                                value="{{ old('minimum_deposit', (int) $finance->MinimumDeposit) }}"
                                class="w-full h-12 ps-4 border rounded-lg border-slate-200 text-slate-600 focus:border-blue-500 focus:outline-none">
                        </div>

                        <div>
                            <label for="allow_below_minimum_deposit" class="block mb-2 text-sm text-gray-700">{{ __('Allow below minimum deposit') }}</label>
                            <select id="allow_below_minimum_deposit" name="allow_below_minimum_deposit"
                                class="w-full h-12 ps-4 border rounded-lg border-slate-200 text-slate-600 focus:border-blue-500 focus:outline-none">
                                <option value="1"
                                    {{ old('allow_below_minimum_deposit', $finance->AllowBelowMinimumDeposit) == 1 ? 'selected' : '' }}>{{ __('Yes') }}</option>
                                <option value="0"
                                    {{ old('allow_below_minimum_deposit', $finance->AllowBelowMinimumDeposit) == 0 ? 'selected' : '' }}>{{ __('No') }}</option>
                            </select>
                        </div>

                        <div>
                            <label for="have_shirt" class="block mb-2 text-sm text-gray-700">{{ __('Has a T-shirt?') }}</label>
                            <select id="have_shirt" name="have_shirt"
                                class="w-full h-12 ps-4 border rounded-lg border-slate-200 text-slate-600 focus:border-blue-500 focus:outline-none">
                                <option value="1" {{ old('have_shirt', $finance->HaveShirt) == 1 ? 'selected' : '' }}>{{ __('Yes') }}</option>
                                <option value="0" {{ old('have_shirt', $finance->HaveShirt) == 0 ? 'selected' : '' }}>{{ __('No') }}</option>
                            </select>
                        </div>
                        <div>
                            <label for="send_qr_whatsapp" class="block mb-2 text-sm text-gray-700">{{ __('Send QR via WhatsApp?') }}</label>
                            <select id="send_qr_whatsapp" name="send_qr_whatsapp"
                                class="w-full h-12 ps-4 border rounded-lg border-slate-200 text-slate-600 focus:border-blue-500 focus:outline-none">
                                <option value="1" {{ old('send_qr_whatsapp', $finance->SendQrWhatsApp ?? 0) == 1 ? 'selected' : '' }}>{{ __('Yes') }}</option>
                                <option value="0" {{ old('send_qr_whatsapp', $finance->SendQrWhatsApp ?? 0) == 0 ? 'selected' : '' }}>{{ __('No') }}</option>
                            </select>
                        </div>
                    </div>

                    @include('finance._interval_rows', ['addButtonClass' => 'bg-green-600 hover:bg-green-700'])

                    <div class="flex justify-center">
                        <button type="submit"
                            class="inline-flex items-center justify-center h-12 px-8 text-sm font-medium tracking-wide rounded-full bg-green-50 text-green-600 hover:bg-green-100 transition">{{ __('Save changes') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            FinanceIntervals.setSectors(@json($sectors));

            const oldIntervals = Object.values(@json(old('intervals', [])) || {});
            const rows = oldIntervals.length ? oldIntervals : @json($intervals);
            rows.forEach(row => FinanceIntervals.addRow(row || {}));

            FinanceIntervals.ensureOne();
        });
    </script>
@endsection
