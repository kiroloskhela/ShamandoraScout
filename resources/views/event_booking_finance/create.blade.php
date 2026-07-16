@extends('layouts.app', ['pageTitle' => __('Create new booking')])

@section('content')
    <div class="container mx-auto px-4 py-8" dir="rtl">
        <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-5xl mx-auto border-2 border-blue-300">
            <div class="mb-6 text-center">
                <h2 class="text-xl font-bold text-gray-800">{{ __('Create new booking + first payment') }}</h2>
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
                <div><strong>{{ __('Season:') }}</strong> {{ $event->SeasonName }} ({{ $event->SeasonYear }})</div>
                <div><strong>{{ __('Event:') }}</strong> {{ $event->EventTypeName }} - {{ $event->EventName }}</div>
                <div><strong>{{ __('Event start:') }}</strong> {{ $event->EventStartDate }}</div>
                <div><strong>{{ __('Event end:') }}</strong> {{ $event->EventEndDate }}</div>
                <div><strong>{{ __('Max installments:') }}</strong> {{ $plan->MaxInstallmentsNumber }}</div>
                <div><strong>{{ __('Minimum deposit:') }}</strong> {{ number_format($plan->MinimumDeposit, 2) }}</div>
                <div><strong>{{ __('Allow below minimum deposit:') }}</strong> {{ $plan->AllowBelowMinimumDeposit ? __('Yes') : __('No') }}</div>
            </div>

            <form method="POST" action="{{ route('eventBookingFinance.store', $event->SeasonEventID) }}">
                @csrf

                <div class="space-y-6">
                    <div>
                        <label class="block mb-2 text-sm text-gray-700">{{ __('Search for eligible person') }}</label>
                        <input type="text" id="person-search"
                            class="w-full h-12 px-4 border rounded-lg text-right border-slate-200 text-slate-600 focus:border-blue-500 focus:outline-none"
                            placeholder="{{ __('Search by name, PersonID, or mobile') }}">
                        <input type="hidden" name="person_id" id="person_id" value="{{ old('person_id') }}">
                        <div id="search-results"
                            class="mt-2 border rounded-lg bg-white shadow hidden max-h-80 overflow-y-auto"></div>
                    </div>

                    <div id="selected-person-box"
                        class="hidden rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-900">
                        <div><strong>{{ __('Name:') }}</strong> <span id="selected-person-name"></span></div>
                        <div><strong>PersonID:</strong> <span id="selected-person-id"></span></div>
                        <div><strong>{{ __('Mobile:') }}</strong> <span id="selected-person-mobile"></span></div>
                        <div><strong>{{ __('Sector:') }}</strong> <span id="selected-person-qetaa"></span></div>
                        <div><strong>{{ __('Status:') }}</strong> <span id="selected-person-status"></span></div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block mb-2 text-sm text-gray-700">{{ __('First payment date') }}</label>
                            <input type="text" value="{{ now()->format('Y-m-d') }}" readonly
                                class="w-full h-12 px-4 border rounded-lg text-right bg-gray-100 border-slate-200 text-slate-600 cursor-not-allowed">

                            <input type="hidden" name="first_payment_date" value="{{ now()->format('Y-m-d') }}">
                        </div>

                        <div>
                            <label class="block mb-2 text-sm text-gray-700">{{ __('First payment amount') }}</label>
                            <input type="number" step="0.01" min="0.01" name="first_payment_amount"
                                value="{{ old('first_payment_amount') }}" id="first_payment_amount"
                                class="w-full h-12 px-4 border rounded-lg text-right border-slate-200 text-slate-600 focus:border-blue-500 focus:outline-none">
                        </div>
                        @if ((int) $plan->HaveShirt === 1)
                            <div>
                                <label class="block mb-2 text-sm text-gray-700">{{ __('T-shirt size') }}</label>
                                <select name="shirt_size" id="shirt_size"
                                    class="w-full h-12 px-4 border rounded-lg text-right border-slate-200 text-slate-600 focus:border-blue-500 focus:outline-none">
                                    <option value="">{{ __('-- Choose size --') }}</option>
                                    <option value="XS" {{ old('shirt_size') === 'XS' ? 'selected' : '' }}>XS</option>
                                    <option value="S" {{ old('shirt_size') === 'S' ? 'selected' : '' }}>S</option>
                                    <option value="M" {{ old('shirt_size') === 'M' ? 'selected' : '' }}>M</option>
                                    <option value="L" {{ old('shirt_size') === 'L' ? 'selected' : '' }}>L</option>
                                    <option value="XL" {{ old('shirt_size') === 'XL' ? 'selected' : '' }}>XL</option>
                                    <option value="2XL" {{ old('shirt_size') === '2XL' ? 'selected' : '' }}>2XL</option>
                                    <option value="3XL" {{ old('shirt_size') === '3XL' ? 'selected' : '' }}>3XL</option>
                                    <option value="4XL" {{ old('shirt_size') === '4XL' ? 'selected' : '' }}>4XL</option>
                                    <option value="5XL" {{ old('shirt_size') === '5XL' ? 'selected' : '' }}>5XL</option>
                                    <option value="6XL" {{ old('shirt_size') === '6XL' ? 'selected' : '' }}>6XL</option>
                                </select>
                            </div>
                        @endif
                    </div>

                    <div class="rounded-lg border border-indigo-200 bg-indigo-50 p-4 text-sm text-indigo-900">
                        <div><strong>{{ __('Current installment number:') }}</strong> 1</div>
                        <div><strong>{{ __('Max installments in the plan:') }}</strong> {{ $plan->MaxInstallmentsNumber }}</div>

                        @if ((int) $plan->MaxInstallmentsNumber === 1)
                            <div class="mt-2 text-red-700 font-bold">{{ __('This event has only one installment, so the full amount must be paid in the first payment.') }}</div>
                        @else
                            <div class="mt-2 text-green-700">
                                {{ __('After recording the first payment, remaining installments can be added up to :count installments.', ['count' => $plan->MaxInstallmentsNumber]) }}
                            </div>
                        @endif
                    </div>

                    <div class="rounded-lg border border-orange-200 bg-orange-50 p-4">
                        <div class="mb-4">
                            <label class="inline-flex items-center gap-2">
                                <input type="checkbox" id="is_not_able_to_pay_all" name="is_not_able_to_pay_all"
                                    value="1" {{ old('is_not_able_to_pay_all') ? 'checked' : '' }}>
                                <span class="text-sm text-gray-800">{{ __('Unable to pay the full amount') }}</span>
                            </label>
                        </div>
                        <input type="hidden" name="booking_type" value="PERSON">
                        <div id="special-options-box" class="{{ old('is_not_able_to_pay_all') ? '' : 'hidden' }}">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <label class="block mb-2 text-sm text-gray-700">{{ __('Case type') }}</label>
                                    <select name="special_case_type" id="special_case_type"
                                        class="w-full h-12 px-4 border rounded-lg text-right border-slate-200 text-slate-600">
                                        <option value="NONE">{{ __('None') }}</option>
                                        <option value="AKHOH_RAB"
                                            {{ old('special_case_type') === 'AKHOH_RAB' ? 'selected' : '' }}>{{ __('Brotherhood case') }}</option>
                                        <option value="HAS_BROTHERS"
                                            {{ old('special_case_type') === 'HAS_BROTHERS' ? 'selected' : '' }}>{{ __('Has brothers') }}</option>
                                        <option value="OTHER"
                                            {{ old('special_case_type') === 'OTHER' ? 'selected' : '' }}>{{ __('Other') }}</option>
                                    </select>
                                </div>

                                <div id="discount-box"
                                    class="{{ in_array(old('special_case_type'), ['AKHOH_RAB', 'HAS_BROTHERS', 'OTHER']) ? '' : 'hidden' }}">
                                    <label class="block mb-2 text-sm text-gray-700">{{ __('Discount amount') }}</label>
                                    <input type="number" step="0.01" min="0" name="discount_amount"
                                        id="discount_amount" value="{{ old('discount_amount', 0) }}"
                                        class="w-full h-12 px-4 border rounded-lg text-right border-slate-200 text-slate-600">
                                </div>

                                <div id="note-box"
                                    class="{{ old('special_case_type') === 'OTHER' || old('special_case_type') === 'AKHOH_RAB' ? '' : 'hidden' }}">
                                    <label class="block mb-2 text-sm text-gray-700">{{ __('Notes') }}</label>
                                    <input type="text" name="special_case_note" id="special_case_note"
                                        value="{{ old('special_case_note') }}"
                                        class="w-full h-12 px-4 border rounded-lg text-right border-slate-200 text-slate-600">
                                </div>
                            </div>
                        </div>
                    </div>

                    @if ((int) $plan->AllowBelowMinimumDeposit === 1)
                        <div class="rounded-lg border border-yellow-200 bg-yellow-50 p-4 text-sm text-yellow-800">{{ __('If the first payment is below the minimum deposit, only a warning is shown and you can continue.') }}</div>
                    @endif

                    <div class="flex justify-center gap-3">
                        <a href="{{ route('eventBookingFinance.index', $event->SeasonEventID) }}"
                            class="inline-flex items-center justify-center h-12 px-8 text-sm font-medium rounded-full bg-gray-100 text-gray-700 hover:bg-gray-200 transition">{{ __('Back') }}</a>

                        <button type="submit" id="submit-btn"
                            class="inline-flex items-center justify-center h-12 px-8 text-sm font-medium rounded-full bg-blue-50 text-blue-500 hover:bg-blue-100 hover:text-blue-600 transition">{{ __('Save and print receipt') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('person-search');
            const resultsBox = document.getElementById('search-results');
            const personIdInput = document.getElementById('person_id');
            const selectedBox = document.getElementById('selected-person-box');
            const selectedName = document.getElementById('selected-person-name');
            const selectedId = document.getElementById('selected-person-id');
            const selectedMobile = document.getElementById('selected-person-mobile');
            const selectedQetaa = document.getElementById('selected-person-qetaa');
            const selectedStatus = document.getElementById('selected-person-status');
            const firstPaymentAmountInput = document.getElementById('first_payment_amount');
            const submitBtn = document.getElementById('submit-btn');
            const minimumDeposit = {{ (float) $plan->MinimumDeposit }};
            const allowBelowMinimum = {{ (int) $plan->AllowBelowMinimumDeposit }};
            const specialCheckbox = document.getElementById('is_not_able_to_pay_all');
            const specialBox = document.getElementById('special-options-box');
            const specialType = document.getElementById('special_case_type');
            const discountBox = document.getElementById('discount-box');
            const noteBox = document.getElementById('note-box');

            function updateSpecialUI() {
                if (specialCheckbox.checked) {
                    specialBox.classList.remove('hidden');
                } else {
                    specialBox.classList.add('hidden');
                }

                const val = specialType.value;

                if (val === 'AKHOH_RAB' || val === 'HAS_BROTHERS' || val === 'OTHER') {
                    discountBox.classList.remove('hidden');
                } else {
                    discountBox.classList.add('hidden');
                }

                if (val === 'OTHER' || val === 'AKHOH_RAB') {
                    noteBox.classList.remove('hidden');
                } else {
                    noteBox.classList.add('hidden');
                }
            }

            specialCheckbox.addEventListener('change', updateSpecialUI);
            specialType.addEventListener('change', updateSpecialUI);
            updateSpecialUI();

            let timeout = null;

            function escapeHtml(str) {
                return String(str ?? '').replace(/[&<>"']/g, s => ({
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#39;'
                }[s]));
            }

            searchInput.addEventListener('input', function() {
                const q = this.value.trim();

                clearTimeout(timeout);

                if (q.length < 1) {
                    resultsBox.classList.add('hidden');
                    resultsBox.innerHTML = '';
                    return;
                }

                timeout = setTimeout(() => {
                    fetch(
                            `{{ route('eventBookingFinance.searchEligiblePersons', $event->SeasonEventID) }}?q=${encodeURIComponent(q)}`
                        )
                        .then(res => res.json())
                        .then(data => {
                            resultsBox.innerHTML = '';
                            resultsBox.classList.remove('hidden');

                            if (!data.length) {
                                resultsBox.innerHTML =
                                    '<div class="p-3 text-sm text-gray-500">{{ __('No results') }}</div>';
                                return;
                            }

                            data.forEach(person => {
                                const item = document.createElement('div');
                                const disabled = parseInt(person.IsBlacklisted) === 1 ||
                                    parseInt(person.AlreadyBooked) === 1;
                                item.className = 'p-3 border-b last:border-b-0';

                                let statusBadges = '';
                                if (parseInt(person.IsBlacklisted) === 1) {
                                    statusBadges +=
                                        '<span class="inline-block bg-red-100 text-red-700 px-2 py-1 rounded text-xs ml-1">{{ __('Blacklisted') }}</span>';
                                }
                                if (parseInt(person.IsSpecialCase) === 1) {
                                    statusBadges +=
                                        '<span class="inline-block bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-xs ml-1">{{ __('Brotherhood case') }}</span>';
                                }
                                if (parseInt(person.AlreadyBooked) === 1) {
                                    statusBadges +=
                                        '<span class="inline-block bg-gray-100 text-gray-700 px-2 py-1 rounded text-xs ml-1">{{ __('Already booked') }}</span>';
                                }

                                const content = `
                                    <div class="${disabled ? 'line-through text-gray-400' : 'text-gray-800 cursor-pointer'}">
                                        <div class="font-bold">${escapeHtml(person.PersonFullName)}</div>
                                        <div class="text-xs mt-1">PersonID: ${escapeHtml(person.PersonID)}</div>
                                        <div class="text-xs">${@json(__('Mobile:'))} ${escapeHtml(person.PersonPersonalMobileNumber ?? '-')}</div>
                                        <div class="text-xs">${@json(__('Sector:'))} ${escapeHtml(person.QetaaNames ?? '-')}</div>
                                        <div class="mt-2">${statusBadges}</div>
                                    </div>
                                `;

                                item.innerHTML = content;

                                if (!disabled) {
                                    item.addEventListener('click', function() {
                                        personIdInput.value = person.PersonID;
                                        searchInput.value = person
                                            .PersonFullName;
                                        selectedName.textContent = person
                                            .PersonFullName;
                                        selectedId.textContent = person
                                            .PersonID;
                                        selectedMobile.textContent = person
                                            .PersonPersonalMobileNumber ?? '-';
                                        selectedQetaa.textContent = person
                                            .QetaaNames ?? '-';
                                        selectedStatus.textContent = parseInt(
                                                person.IsSpecialCase) === 1 ?
                                            @json(__('Brotherhood case')) : @json(__('Normal'));
                                        selectedBox.classList.remove('hidden');
                                        resultsBox.classList.add('hidden');
                                    });
                                }

                                resultsBox.appendChild(item);
                            });
                        });
                }, 250);
            });

            document.addEventListener('click', function(e) {
                if (!resultsBox.contains(e.target) && e.target !== searchInput) {
                    resultsBox.classList.add('hidden');
                }
            });

            submitBtn.addEventListener('click', function(e) {
                const isSpecial = specialCheckbox.checked && specialType.value === 'AKHOH_RAB';
                const amount = parseFloat(firstPaymentAmountInput.value || 0);

                if (!isSpecial && allowBelowMinimum === 1 && amount > 0 && amount < minimumDeposit) {
                    const ok = confirm(@json(__('This payment is below the minimum deposit. Do you want to continue?')));
                    if (!ok) {
                        e.preventDefault();
                    }
                }
            });
        });
    </script>
@endsection
