@extends('layouts.app', ['pageTitle' => 'إضافة حجز ضيف / أهالي'])

@section('content')
    <div class="container mx-auto px-4 py-6" dir="rtl">
        @if ($errors->has('general'))
            <div class="mb-4 rounded-lg bg-red-100 border border-red-300 text-red-800 px-4 py-3">
                {{ $errors->first('general') }}
            </div>
        @endif

        <div class="mb-4 bg-white rounded-2xl shadow-sm border border-slate-200 p-4">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="min-w-0">
                    <h2 class="text-lg font-bold text-slate-800">إضافة حجز ضيف / أهالي</h2>

                    <div class="mt-2 flex flex-wrap gap-2 text-xs text-slate-600">
                        <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1">
                            الموسم: {{ $event->SeasonName }} ({{ $event->SeasonYear }})
                        </span>

                        <span class="inline-flex items-center rounded-full bg-blue-50 text-blue-700 px-3 py-1">
                            {{ $event->EventTypeName }} - {{ $event->EventName }}
                        </span>

                        <span class="inline-flex items-center rounded-full bg-violet-50 text-violet-700 px-3 py-1">
                            الحد الأقصى للأقساط: {{ $plan->MaxInstallmentsNumber }}
                        </span>

                        <span class="inline-flex items-center rounded-full bg-emerald-50 text-emerald-700 px-3 py-1">
                            الحد الأدنى للمقدم: {{ number_format((float) $plan->MinimumDeposit, 2) }}
                        </span>
                    </div>
                </div>

                <div class="flex gap-2 shrink-0">
                    <a href="{{ route('eventBookingFinance.index', $event->SeasonEventID) }}"
                        class="bg-gray-100 hover:bg-gray-200 text-gray-800 text-sm font-bold py-2 px-4 rounded-lg transition-colors duration-200">
                        رجوع
                    </a>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('eventBookingFinance.store', $event->SeasonEventID) }}"
            class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 space-y-6">
            @csrf

            <div>
                <h3 class="text-base font-extrabold text-slate-800 mb-4">نوع الحجز</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <label class="rounded-2xl border border-slate-200 p-4 cursor-pointer hover:border-blue-400 transition">
                        <div class="flex items-start gap-3">
                            <input type="radio" name="booking_type" value="GUEST" class="mt-1 booking-type-radio"
                                {{ old('booking_type', 'GUEST') === 'GUEST' ? 'checked' : '' }}>
                            <div>
                                <div class="font-bold text-slate-800">ضيف</div>
                                <div class="text-xs text-slate-500 mt-1">الحجز لضيف وسيظهر قطاعه ضيوف</div>
                            </div>
                        </div>
                    </label>

                    <label class="rounded-2xl border border-slate-200 p-4 cursor-pointer hover:border-blue-400 transition">
                        <div class="flex items-start gap-3">
                            <input type="radio" name="booking_type" value="FAMILY" class="mt-1 booking-type-radio"
                                {{ old('booking_type') === 'FAMILY' ? 'checked' : '' }}>
                            <div>
                                <div class="font-bold text-slate-800">أهالي</div>
                                <div class="text-xs text-slate-500 mt-1">الحجز لفرد من العائلة وسيظهر قطاعه اهالي</div>
                            </div>
                        </div>
                    </label>
                </div>

                @error('booking_type')
                    <p class="text-red-600 text-xs mt-2">{{ $message }}</p>
                @enderror
            </div>

            <div class="rounded-2xl border border-slate-200 p-4 bg-slate-50">
                <h3 class="text-sm font-extrabold text-slate-800 mb-4">البحث والاختيار</h3>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                    <div class="lg:col-span-2">
                        <label class="block text-sm font-bold text-slate-700 mb-2">ابحث</label>
                        <input type="text" id="search_input"
                            class="w-full h-12 px-4 rounded-xl border border-slate-200 focus:border-blue-500 focus:outline-none"
                            placeholder="اكتب الاسم أو الكود أو الموبايل أو الرقم القومي">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">الاختيار الحالي</label>
                        <div id="selected_summary"
                            class="h-12 px-4 rounded-xl border border-slate-200 bg-white flex items-center text-sm text-slate-500">
                            لم يتم اختيار أي شخص بعد
                        </div>
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
                <h3 class="text-sm font-extrabold text-slate-800 mb-4">بيانات الحجز</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">تاريخ أول دفعة</label>
                        <input type="date" name="first_payment_date"
                            value="{{ old('first_payment_date', now()->format('Y-m-d')) }}"
                            class="w-full h-11 px-4 rounded-xl border border-slate-200 focus:border-blue-500 focus:outline-none">
                        @error('first_payment_date')
                            <p class="text-red-600 text-xs mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">مبلغ أول دفعة</label>
                        <input type="number" step="0.01" min="0.01" name="first_payment_amount"
                            value="{{ old('first_payment_amount') }}"
                            class="w-full h-11 px-4 rounded-xl border border-slate-200 focus:border-blue-500 focus:outline-none">
                        @error('first_payment_amount')
                            <p class="text-red-600 text-xs mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">مقاس التيشيرت</label>
                        <select name="shirt_size"
                            class="w-full h-11 px-4 rounded-xl border border-slate-200 focus:border-blue-500 focus:outline-none">
                            <option value="">اختر المقاس</option>
                            <option value="XS" {{ old('shirt_size') === 'XS' ? 'selected' : '' }}>XS</option>
                            <option value="S" {{ old('shirt_size') === 'S' ? 'selected' : '' }}>S</option>
                            <option value="M" {{ old('shirt_size') === 'M' ? 'selected' : '' }}>M</option>
                            <option value="L" {{ old('shirt_size') === 'L' ? 'selected' : '' }}>L</option>
                            <option value="XL" {{ old('shirt_size') === 'XL' ? 'selected' : '' }}>XL</option>
                            <option value="2XL" {{ old('shirt_size') === '2XL' ? 'selected' : '' }}>2XL</option>
                            <option value="3XL" {{ old('shirt_size') === '3XL' ? 'selected' : '' }}>3XL</option>
                            <option value="4XL" {{ old('shirt_size') === '4XL' ? 'selected' : '' }}>4XL</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">الخصم</label>
                        <input type="number" step="0.01" min="0" name="discount_amount"
                            value="{{ old('discount_amount', 0) }}"
                            class="w-full h-11 px-4 rounded-xl border border-slate-200 focus:border-blue-500 focus:outline-none">
                        @error('discount_amount')
                            <p class="text-red-600 text-xs mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mt-4">
                    <label class="block text-sm font-bold text-slate-700 mb-2">ملاحظات عامة</label>
                    <textarea name="notes" rows="3"
                        class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-blue-500 focus:outline-none"
                        placeholder="أي ملاحظات على الحجز">{{ old('notes') }}</textarea>
                </div>
            </div>

            <div class="flex flex-wrap gap-3 justify-end">
                <a href="{{ route('eventBookingFinance.index', $event->SeasonEventID) }}"
                    class="px-5 h-11 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-800 text-sm font-bold inline-flex items-center">
                    إلغاء
                </a>

                <button type="submit"
                    class="px-5 h-11 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold inline-flex items-center">
                    حفظ الحجز
                </button>
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
                    return "{{ route('eventBookingFinance.searchEligibleFamilies', $event->SeasonEventID) }}";
                }

                return "{{ route('eventBookingFinance.searchEligibleGuests', $event->SeasonEventID) }}";
            }

            function resetSelectedIds() {
                personIdInput.value = '';
                guestIdInput.value = '';
                familyIdInput.value = '';
                selectedSummary.innerHTML = 'لم يتم اختيار أي شخص بعد';
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
                        <span class="font-bold text-slate-800">${item.PersonFullName ?? '-'}</span>
                        <span class="inline-flex items-center rounded-full bg-blue-50 text-blue-700 px-2 py-1 text-xs">${buildCode(item)}</span>
                        <span class="inline-flex items-center rounded-full bg-slate-100 text-slate-700 px-2 py-1 text-xs">${item.QetaaNames ?? '-'}</span>
                        <span class="text-slate-500 text-xs">${item.PersonPersonalMobileNumber ?? '-'}</span>
                    </div>
                `;
            }

            function renderResults(items) {
                if (!items.length) {
                    resultsBox.innerHTML = `
                        <div class="rounded-xl border border-dashed border-slate-300 bg-white p-4 text-sm text-slate-500 text-center">
                            لا توجد نتائج
                        </div>
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
                                        <span class="font-bold text-slate-800">${item.PersonFullName ?? '-'}</span>
                                        <span class="inline-flex items-center rounded-full bg-blue-50 text-blue-700 px-2 py-1 text-xs">${code}</span>
                                        <span class="inline-flex items-center rounded-full bg-slate-100 text-slate-700 px-2 py-1 text-xs">${item.QetaaNames ?? '-'}</span>
                                    </div>

                                    <div class="flex flex-wrap gap-2 text-xs text-slate-500">
                                        <span>الموبايل: ${item.PersonPersonalMobileNumber ?? '-'}</span>
                                        ${booked ? '<span class="text-amber-700 font-bold">محجوز بالفعل</span>' : ''}
                                    </div>
                                </div>

                                <div>
                                    <button type="button"
                                        class="px-4 h-10 rounded-xl ${booked ? 'bg-gray-300 text-gray-600 cursor-not-allowed' : 'bg-blue-600 hover:bg-blue-700 text-white'} text-sm font-bold"
                                        ${booked ? 'disabled' : ''}
                                        data-payload='${JSON.stringify(item).replace(/'/g, "&apos;")}'>
                                        اختيار
                                    </button>
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
                    <div class="rounded-xl border border-slate-200 bg-white p-4 text-sm text-slate-500 text-center">
                        جاري البحث...
                    </div>
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
                        <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700 text-center">
                            حدث خطأ أثناء البحث
                        </div>
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
