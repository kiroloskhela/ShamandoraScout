@extends('layouts.app', ['pageTitle' => __('Add guest / family booking')])

@section('content')
    <div class="container mx-auto px-4 py-6">
        @if ($errors->has('general'))
            <div class="mb-4 rounded-lg bg-red-100 border border-red-300 text-red-800 px-4 py-3">
                {{ $errors->first('general') }}
            </div>
        @endif

        <div class="mb-4 bg-white rounded-2xl shadow-sm border border-slate-200 p-4">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="min-w-0">
                    <h2 class="text-lg font-bold text-slate-800">{{ __('Add guest / family booking') }}</h2>

                    <div class="mt-2 flex flex-wrap gap-2 text-xs text-slate-600">
                        <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1">
                            {{ __('Season:') }} {{ $event->SeasonName }} ({{ $event->SeasonYear }})
                        </span>

                        <span class="inline-flex items-center rounded-full bg-blue-50 text-blue-700 px-3 py-1">
                            {{ $event->EventTypeName }} - {{ $event->EventName }}
                        </span>

                        <span class="inline-flex items-center rounded-full bg-violet-50 text-violet-700 px-3 py-1">
                            {{ __('Max installments:') }} {{ $plan->MaxInstallmentsNumber }}
                        </span>

                        <span class="inline-flex items-center rounded-full bg-emerald-50 text-emerald-700 px-3 py-1">
                            {{ __('Minimum deposit:') }} {{ number_format((float) $plan->MinimumDeposit, 2) }}
                        </span>
                    </div>
                </div>

                <div class="flex gap-2 shrink-0">
                    <a href="{{ route('eventBookingFinance.index', $event->SeasonEventID) }}"
                        class="bg-gray-100 hover:bg-gray-200 text-gray-800 text-sm font-bold py-2 px-4 rounded-lg transition-colors duration-200">{{ __('Back') }}</a>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('eventBookingFinance.store', $event->SeasonEventID) }}"
            data-skip-loading="1"
            class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 space-y-6">
            @csrf

            <div>
                <h3 class="text-base font-extrabold text-slate-800 mb-4">{{ __('Booking type') }}</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <label class="rounded-2xl border border-slate-200 p-4 cursor-pointer hover:border-blue-400 transition">
                        <div class="flex items-start gap-3">
                            <input type="radio" name="booking_type" value="GUEST" class="mt-1 booking-type-radio"
                                {{ old('booking_type', 'GUEST') === 'GUEST' ? 'checked' : '' }}>
                            <div>
                                <div class="font-bold text-slate-800">{{ __('Guest') }}</div>
                                <div class="text-xs text-slate-500 mt-1">{{ __('Guest booking; sector will show as Guests') }}</div>
                            </div>
                        </div>
                    </label>

                    <label class="rounded-2xl border border-slate-200 p-4 cursor-pointer hover:border-blue-400 transition">
                        <div class="flex items-start gap-3">
                            <input type="radio" name="booking_type" value="FAMILY" class="mt-1 booking-type-radio"
                                {{ old('booking_type') === 'FAMILY' ? 'checked' : '' }}>
                            <div>
                                <div class="font-bold text-slate-800">{{ __('Families') }}</div>
                                <div class="text-xs text-slate-500 mt-1">{{ __('Booking for a family member; sector will show as Families') }}</div>
                            </div>
                        </div>
                    </label>
                </div>

                @error('booking_type')
                    <p class="text-red-600 text-xs mt-2">{{ $message }}</p>
                @enderror
            </div>

            <div class="rounded-2xl border border-slate-200 p-4 bg-slate-50">
                <h3 class="text-sm font-extrabold text-slate-800 mb-4">{{ __('Search and select') }}</h3>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                    <div class="lg:col-span-2">
                        <label class="block text-sm font-bold text-slate-700 mb-2">{{ __('Search') }}</label>
                        <input type="text" id="search_input"
                            class="w-full h-12 px-4 rounded-xl border border-slate-200 focus:border-blue-500 focus:outline-none"
                            placeholder="{{ __('Type name, code, mobile, or national ID') }}">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">{{ __('Current selection') }}</label>
                        <div id="selected_summary"
                            class="h-12 px-4 rounded-xl border border-slate-200 bg-white flex items-center text-sm text-slate-500">{{ __('No person selected yet') }}</div>
                    </div>
                </div>

                <div id="search_results" class="mt-4 grid grid-cols-1 gap-3"></div>

                <input type="hidden" name="person_id" id="person_id" value="">
                <input type="hidden" name="guest_id" id="guest_id" value="{{ old('guest_id') }}">
                <input type="hidden" name="family_id" id="family_id" value="{{ old('family_id') }}">

                @error('guest_id')
                    <p class="text-red-600 text-xs mt-2">{{ $message }}</p>
                @enderror

                @error('family_id')
                    <p class="text-red-600 text-xs mt-2">{{ $message }}</p>
                @enderror
            </div>

            <div class="rounded-2xl border border-slate-200 p-4">
                <h3 class="text-sm font-extrabold text-slate-800 mb-4">{{ __('Booking details') }}</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">{{ __('First payment date') }}</label>
                        <input type="date" name="first_payment_date"
                            value="{{ old('first_payment_date', now()->format('Y-m-d')) }}"
                            class="w-full h-11 px-4 rounded-xl border border-slate-200 focus:border-blue-500 focus:outline-none">
                        @error('first_payment_date')
                            <p class="text-red-600 text-xs mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">{{ __('First payment amount') }}</label>
                        <input type="number" step="1" min="0" name="first_payment_amount"
                            value="{{ old('first_payment_amount') }}"
                            class="w-full h-11 px-4 rounded-xl border border-slate-200 focus:border-blue-500 focus:outline-none">
                        @error('first_payment_amount')
                            <p class="text-red-600 text-xs mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">{{ __('T-shirt size') }}</label>
                        <div class="relative">
                            <select name="shirt_size"
                                class="w-full h-11 ps-4 pe-10 rounded-xl border border-slate-200 bg-white focus:border-blue-500 focus:outline-none appearance-none cursor-pointer">
                                <option value="">{{ __('Choose size') }}</option>
                                <option value="XS" {{ old('shirt_size') === 'XS' ? 'selected' : '' }}>XS</option>
                                <option value="S" {{ old('shirt_size') === 'S' ? 'selected' : '' }}>S</option>
                                <option value="M" {{ old('shirt_size') === 'M' ? 'selected' : '' }}>M</option>
                                <option value="L" {{ old('shirt_size') === 'L' ? 'selected' : '' }}>L</option>
                                <option value="XL" {{ old('shirt_size') === 'XL' ? 'selected' : '' }}>XL</option>
                                <option value="2XL" {{ old('shirt_size') === '2XL' ? 'selected' : '' }}>2XL</option>
                                <option value="3XL" {{ old('shirt_size') === '3XL' ? 'selected' : '' }}>3XL</option>
                                <option value="4XL" {{ old('shirt_size') === '4XL' ? 'selected' : '' }}>4XL</option>
                            </select>
                            <svg class="pointer-events-none absolute end-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.17l3.71-3.94a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">{{ __('Discount') }}</label>
                        <input type="number" step="1" min="0" name="discount_amount"
                            value="{{ old('discount_amount', 0) }}"
                            class="w-full h-11 px-4 rounded-xl border border-slate-200 focus:border-blue-500 focus:outline-none">
                        @error('discount_amount')
                            <p class="text-red-600 text-xs mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mt-4">
                    <label class="block text-sm font-bold text-slate-700 mb-2">{{ __('General notes') }}</label>
                    <textarea name="notes" rows="3"
                        class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-blue-500 focus:outline-none"
                        placeholder="{{ __('Any notes on the booking') }}">{{ old('notes') }}</textarea>
                </div>
            </div>

            <div class="flex flex-wrap gap-3 justify-end">
                <a href="{{ route('eventBookingFinance.index', $event->SeasonEventID) }}"
                    class="px-5 h-11 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-800 text-sm font-bold inline-flex items-center">{{ __('Cancel') }}</a>

                <button type="submit"
                    class="px-5 h-11 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold inline-flex items-center">{{ __('Save booking') }}</button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('search_input');
            const resultsBox = document.getElementById('search_results');
            const selectedSummary = document.getElementById('selected_summary');

            const personIdInput = document.getElementById('person_id');
            const guestIdInput = document.getElementById('guest_id');
            const familyIdInput = document.getElementById('family_id');

            const bookingTypeInputs = document.querySelectorAll('.booking-type-radio');

            function getBookingType() {
                const checked = document.querySelector('.booking-type-radio:checked');
                return checked ? checked.value : 'GUEST';
            }

            function getEndpoint() {
                const type = getBookingType();

                if (type === 'FAMILY') {
                    return "{{ route('eventBookingFinance.searchFamilies', ['seasonEventID' => $seasonEventID]) }}";
                }

                return "{{ route('eventBookingFinance.searchGuests', ['seasonEventID' => $seasonEventID]) }}";
            }

            function resetSelectedIds() {
                personIdInput.value = '';
                guestIdInput.value = '';
                familyIdInput.value = '';
                selectedSummary.innerHTML = @json(__('No person selected yet'));
            }

            function buildCode(item) {
                const type = getBookingType();

                if (type === 'FAMILY') {
                    return 'FM-' + item.FamilyID;
                }

                return 'GU-' + item.GuestID;
            }

            function chooseItem(item) {
                const type = getBookingType();

                resetSelectedIds();

                if (type === 'FAMILY') {
                    familyIdInput.value = item.FamilyID;
                } else {
                    guestIdInput.value = item.GuestID;
                }

                selectedSummary.innerHTML = `
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="font-bold text-slate-800">${escapeHtml(item.PersonFullName ?? '-')}</span>
                        <span class="inline-flex items-center rounded-full bg-blue-50 text-blue-700 px-2 py-1 text-xs">${escapeHtml(buildCode(item))}</span>
                        <span class="inline-flex items-center rounded-full bg-slate-100 text-slate-700 px-2 py-1 text-xs">${escapeHtml(item.QetaaNames ?? '-')}</span>
                        <span class="text-slate-500 text-xs">${escapeHtml(item.PersonPersonalMobileNumber ?? '-')}</span>
                    </div>
                `;
            }

            function escapeHtml(str) {
                return String(str ?? '').replace(/[&<>"']/g, s => ({
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#39;'
                }[s]));
            }

            function renderResults(items) {
                if (!items.length) {
                    resultsBox.innerHTML = `
                        <div class="rounded-xl border border-dashed border-slate-300 bg-white p-4 text-sm text-slate-500 text-center">{{ __('No results') }}</div>
                    `;
                    return;
                }

                resultsBox.innerHTML = items.map(item => {
                    const code = buildCode(item);
                    const booked = Number(item.AlreadyBooked) === 1;

                    return `
                        <div class="rounded-2xl border ${booked ? 'border-amber-300 bg-amber-50' : 'border-slate-200 bg-white'} p-4">
                            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2 mb-2">
                                        <span class="font-bold text-slate-800">${escapeHtml(item.PersonFullName ?? '-')}</span>
                                        <span class="inline-flex items-center rounded-full bg-blue-50 text-blue-700 px-2 py-1 text-xs">${escapeHtml(code)}</span>
                                        <span class="inline-flex items-center rounded-full bg-slate-100 text-slate-700 px-2 py-1 text-xs">${escapeHtml(item.QetaaNames ?? '-')}</span>
                                    </div>

                                    <div class="flex flex-wrap gap-2 text-xs text-slate-500">
                                        <span>${@json(__('Mobile:'))} ${escapeHtml(item.PersonPersonalMobileNumber ?? '-')}</span>
                                        ${booked ? '<span class="text-amber-700 font-bold">{{ __('Already booked') }}</span>' : ''}
                                    </div>
                                </div>

                                <div>
                                    <button type="button"
                                        class="px-4 h-10 rounded-xl ${booked ? 'bg-gray-300 text-gray-600 cursor-not-allowed' : 'bg-blue-600 hover:bg-blue-700 text-white'} text-sm font-bold"
                                        ${booked ? 'disabled' : ''}
                                        data-payload='${JSON.stringify(item).replace(/'/g, "&apos;")}'>{{ __('Select') }}</button>
                                </div>
                            </div>
                        </div>
                    `;
                }).join('');

                resultsBox.querySelectorAll('button[data-payload]').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const payload = JSON.parse(this.getAttribute('data-payload'));
                        chooseItem(payload);
                    });
                });
            }

            let debounceTimer = null;

            async function performSearch() {
                const query = searchInput.value.trim();
                const endpoint = getEndpoint();

                if (query.length < 1) {
                    resultsBox.innerHTML = '';
                    return;
                }

                resultsBox.innerHTML = `
                    <div class="rounded-xl border border-slate-200 bg-white p-4 text-sm text-slate-500 text-center">{{ __('Searching...') }}</div>
                `;

                try {
                    const response = await fetch(`${endpoint}?q=${encodeURIComponent(query)}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    const data = await response.json();
                    renderResults(Array.isArray(data) ? data : []);
                } catch (error) {
                    resultsBox.innerHTML = `
                        <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700 text-center">{{ __('An error occurred while searching') }}</div>
                    `;
                }
            }

            searchInput.addEventListener('input', function() {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(performSearch, 350);
            });

            bookingTypeInputs.forEach(input => {
                input.addEventListener('change', function() {
                    resetSelectedIds();
                    searchInput.value = '';
                    resultsBox.innerHTML = '';
                });
            });
        });
    </script>
@endpush
