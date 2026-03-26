@extends('layouts.app', ['pageTitle' => 'إضافة خطة مالية'])

@section('content')
    <div class="flex place-content-center">
        <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-5xl border-2 border-blue-300" dir="rtl">
            <div class="mb-6 text-center">
                <h2 class="text-xl font-bold text-gray-800">إضافة خطة مالية لفعالية</h2>
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

            <form method="POST" action="{{ route('finance.store') }}">
                @csrf
                <div class="space-y-6">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="relative">
                            <label for="season_id" class="block mb-2 text-sm text-gray-700">اختر الموسم</label>
                            <select id="season_id" name="season_id" required
                                class="w-full h-12 px-4 border rounded-lg text-right border-slate-200 text-slate-600 focus:border-blue-500 focus:outline-none">
                                <option value="">-- اختر الموسم --</option>
                                @foreach ($seasons as $season)
                                    <option value="{{ $season->SeasonID }}"
                                        {{ old('season_id') == $season->SeasonID ? 'selected' : '' }}>
                                        {{ $season->SeasonName }} ({{ $season->SeasonYear }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="relative">
                            <label for="season_event_id" class="block mb-2 text-sm text-gray-700">اختر الفعالية</label>
                            <select id="season_event_id" name="season_event_id" required
                                class="w-full h-12 px-4 border rounded-lg text-right border-slate-200 text-slate-600 focus:border-blue-500 focus:outline-none">
                                <option value="">-- اختر الفعالية --</option>
                            </select>
                        </div>
                    </div>

                    <div id="event-info-box"
                        class="hidden rounded-lg border border-blue-200 bg-blue-50 p-4 text-sm text-blue-900">
                        <div><strong>الفعالية:</strong> <span id="event_name_text">-</span></div>
                        <div><strong>بداية الفعالية:</strong> <span id="event_start_text">-</span></div>
                        <div><strong>نهاية الفعالية:</strong> <span id="event_end_text">-</span></div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <div>
                            <label for="max_installments_number" class="block mb-2 text-sm text-gray-700">أقصى عدد
                                أقساط</label>
                            <input type="number" min="1" id="max_installments_number" name="max_installments_number"
                                value="{{ old('max_installments_number', 1) }}"
                                class="w-full h-12 px-4 border rounded-lg text-right border-slate-200 text-slate-600 focus:border-blue-500 focus:outline-none">
                        </div>

                        <div>
                            <label for="minimum_deposit" class="block mb-2 text-sm text-gray-700">الحد الأدنى للمقدم</label>
                            <input type="number" step="0.01" min="0" id="minimum_deposit" name="minimum_deposit"
                                value="{{ old('minimum_deposit', 0) }}"
                                class="w-full h-12 px-4 border rounded-lg text-right border-slate-200 text-slate-600 focus:border-blue-500 focus:outline-none">
                        </div>

                        <div>
                            <label for="allow_below_minimum_deposit" class="block mb-2 text-sm text-gray-700">السماح بأقل من
                                المقدم</label>
                            <select id="allow_below_minimum_deposit" name="allow_below_minimum_deposit"
                                class="w-full h-12 px-4 border rounded-lg text-right border-slate-200 text-slate-600 focus:border-blue-500 focus:outline-none">
                                <option value="1"
                                    {{ old('allow_below_minimum_deposit', '1') == '1' ? 'selected' : '' }}>نعم</option>
                                <option value="0" {{ old('allow_below_minimum_deposit') == '0' ? 'selected' : '' }}>لا
                                </option>
                            </select>
                        </div>
                        <div>
                            <label for="have_shirt" class="block mb-2 text-sm text-gray-700">هل يوجد تيشيرت؟</label>
                            <select id="have_shirt" name="have_shirt"
                                class="w-full h-12 px-4 border rounded-lg text-right border-slate-200 text-slate-600 focus:border-blue-500 focus:outline-none">
                                <option value="1" {{ old('have_shirt', '0') == '1' ? 'selected' : '' }}>نعم</option>
                                <option value="0" {{ old('have_shirt', '0') == '0' ? 'selected' : '' }}>لا</option>
                            </select>
                        </div>
                    </div>

                    <div class="border rounded-lg p-4 bg-gray-50">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="font-bold text-gray-800">الفترات السعرية</h3>
                            <button type="button" id="add-interval-btn"
                                class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition-colors duration-200">
                                إضافة فترة سعرية
                            </button>
                        </div>

                        <div id="intervals-container" class="space-y-4"></div>

                        <p class="text-xs text-gray-500 mt-4">
                            ملاحظة: لو آخر فترة انتهت قبل بداية الفعالية، سيتم استكمال الأيام المتبقية تلقائيًا بنفس آخر
                            سعر.
                        </p>
                    </div>

                    <div class="flex justify-center">
                        <button type="submit"
                            class="inline-flex items-center justify-center h-12 px-8 text-sm font-medium tracking-wide rounded-full bg-blue-50 text-blue-500 hover:bg-blue-100 hover:text-blue-600 transition">
                            حفظ الخطة المالية
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const seasonSelect = document.getElementById('season_id');
            const eventSelect = document.getElementById('season_event_id');
            const eventInfoBox = document.getElementById('event-info-box');
            const eventNameText = document.getElementById('event_name_text');
            const eventStartText = document.getElementById('event_start_text');
            const eventEndText = document.getElementById('event_end_text');
            const addIntervalBtn = document.getElementById('add-interval-btn');
            const intervalsContainer = document.getElementById('intervals-container');

            let loadedEvents = [];
            let intervalIndex = 0;

            function createIntervalRow(startValue = '', endValue = '', priceValue = '') {
                const wrapper = document.createElement('div');
                wrapper.className =
                    'grid grid-cols-1 md:grid-cols-4 gap-4 border rounded-lg p-4 bg-white interval-row';

                wrapper.innerHTML = `
            <div>
                <label class="block mb-2 text-sm text-gray-700">من تاريخ</label>
                <input type="date" name="start_date[]" value="${startValue}"
                    class="w-full h-12 px-4 border rounded-lg text-right border-slate-200 text-slate-600 focus:border-blue-500 focus:outline-none" required>
            </div>

            <div>
                <label class="block mb-2 text-sm text-gray-700">إلى تاريخ</label>
                <input type="date" name="end_date[]" value="${endValue}"
                    class="w-full h-12 px-4 border rounded-lg text-right border-slate-200 text-slate-600 focus:border-blue-500 focus:outline-none" required>
            </div>

            <div>
                <label class="block mb-2 text-sm text-gray-700">السعر</label>
                <input type="number" step="0.01" min="0" name="price[]" value="${priceValue}"
                    class="w-full h-12 px-4 border rounded-lg text-right border-slate-200 text-slate-600 focus:border-blue-500 focus:outline-none" required>
            </div>

            <div class="flex items-end">
                <button type="button"
                    class="remove-interval-btn w-full inline-flex items-center justify-center h-12 px-4 text-sm font-medium rounded-lg bg-red-50 text-red-600 hover:bg-red-100 transition">
                    حذف الفترة
                </button>
            </div>
        `;

                intervalsContainer.appendChild(wrapper);

                wrapper.querySelector('.remove-interval-btn').addEventListener('click', function() {
                    wrapper.remove();
                    ensureAtLeastOneInterval();
                });

                intervalIndex++;
            }

            function ensureAtLeastOneInterval() {
                const rows = document.querySelectorAll('.interval-row');
                if (rows.length === 0) {
                    createIntervalRow();
                }
            }

            addIntervalBtn.addEventListener('click', function() {
                createIntervalRow();
            });

            seasonSelect.addEventListener('change', function() {
                const seasonId = this.value;

                eventSelect.innerHTML = '<option value="">-- اختر الفعالية --</option>';
                eventInfoBox.classList.add('hidden');
                loadedEvents = [];

                if (seasonId) {
                    eventSelect.innerHTML = '<option value="">جاري التحميل...</option>';

                    fetch(`{{ route('finance.getEventsForSeason') }}?seasonID=${seasonId}`)
                        .then(response => response.json())
                        .then(events => {
                            loadedEvents = events;
                            eventSelect.innerHTML = '<option value="">-- اختر الفعالية --</option>';

                            events.forEach(event => {
                                const option = document.createElement('option');
                                option.value = event.SeasonEventID;

                                let text =
                                    `${event.EventName} (${event.EventStartDate} → ${event.EventEndDate})`;
                                if (parseInt(event.HasFinancePlan) === 1) {
                                    text += ' - لها خطة مالية بالفعل';
                                }

                                option.textContent = text;
                                option.disabled = parseInt(event.HasFinancePlan) === 1;
                                eventSelect.appendChild(option);
                            });

                            const oldEventId = @json(old('season_event_id'));
                            if (oldEventId) {
                                eventSelect.value = oldEventId;
                                eventSelect.dispatchEvent(new Event('change'));
                            }
                        })
                        .catch(error => {
                            console.error('Error fetching events:', error);
                            eventSelect.innerHTML = '<option value="">خطأ في تحميل الفعاليات</option>';
                        });
                }
            });

            eventSelect.addEventListener('change', function() {
                const selectedEventId = this.value;
                const selectedEvent = loadedEvents.find(e => String(e.SeasonEventID) === String(
                    selectedEventId));

                if (selectedEvent) {
                    eventInfoBox.classList.remove('hidden');
                    eventNameText.textContent = selectedEvent.EventName;
                    eventStartText.textContent = selectedEvent.EventStartDate;
                    eventEndText.textContent = selectedEvent.EventEndDate;
                } else {
                    eventInfoBox.classList.add('hidden');
                }
            });

            const oldIntervalsStart = @json(old('start_date', []));
            const oldIntervalsEnd = @json(old('end_date', []));
            const oldIntervalsPrice = @json(old('price', []));

            if (oldIntervalsStart.length > 0) {
                for (let i = 0; i < oldIntervalsStart.length; i++) {
                    createIntervalRow(
                        oldIntervalsStart[i] ?? '',
                        oldIntervalsEnd[i] ?? '',
                        oldIntervalsPrice[i] ?? ''
                    );
                }
            } else {
                createIntervalRow();
            }

            const oldSeasonId = @json(old('season_id'));
            if (oldSeasonId) {
                seasonSelect.value = oldSeasonId;
                seasonSelect.dispatchEvent(new Event('change'));
            }
        });
    </script>
@endsection
