@extends('layouts.app', ['pageTitle' => 'تعديل الخطة المالية'])

@section('content')
    <div class="flex place-content-center">
        <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-5xl border-2 border-green-300" dir="rtl">
            <div class="mb-6 text-center">
                <h2 class="text-xl font-bold text-gray-800">تعديل الخطة المالية</h2>
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
                <div><strong>الموسم:</strong> {{ $finance->SeasonName }} ({{ $finance->SeasonYear }})</div>
                <div><strong>الفعالية:</strong> {{ $finance->EventName }}</div>
                <div><strong>بداية الفعالية:</strong> {{ $finance->EventStartDate }}</div>
                <div><strong>نهاية الفعالية:</strong> {{ $finance->EventEndDate }}</div>
            </div>

            <form method="POST" action="{{ route('finance.update', $finance->SeasonEventID) }}">
                @csrf
                <div class="space-y-6">

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label for="max_installments_number" class="block mb-2 text-sm text-gray-700">أقصى عدد
                                أقساط</label>
                            <input type="number" min="1" id="max_installments_number" name="max_installments_number"
                                value="{{ old('max_installments_number', $finance->MaxInstallmentsNumber) }}"
                                class="w-full h-12 px-4 border rounded-lg text-right border-slate-200 text-slate-600 focus:border-blue-500 focus:outline-none">
                        </div>

                        <div>
                            <label for="minimum_deposit" class="block mb-2 text-sm text-gray-700">الحد الأدنى للمقدم</label>
                            <input type="number" step="0.01" min="0" id="minimum_deposit" name="minimum_deposit"
                                value="{{ old('minimum_deposit', $finance->MinimumDeposit) }}"
                                class="w-full h-12 px-4 border rounded-lg text-right border-slate-200 text-slate-600 focus:border-blue-500 focus:outline-none">
                        </div>

                        <div>
                            <label for="allow_below_minimum_deposit" class="block mb-2 text-sm text-gray-700">السماح بأقل من
                                المقدم</label>
                            <select id="allow_below_minimum_deposit" name="allow_below_minimum_deposit"
                                class="w-full h-12 px-4 border rounded-lg text-right border-slate-200 text-slate-600 focus:border-blue-500 focus:outline-none">
                                <option value="1"
                                    {{ old('allow_below_minimum_deposit', $finance->AllowBelowMinimumDeposit) == 1 ? 'selected' : '' }}>
                                    نعم</option>
                                <option value="0"
                                    {{ old('allow_below_minimum_deposit', $finance->AllowBelowMinimumDeposit) == 0 ? 'selected' : '' }}>
                                    لا</option>
                            </select>
                        </div>
                    </div>

                    <div class="border rounded-lg p-4 bg-gray-50">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="font-bold text-gray-800">الفترات السعرية</h3>
                            <button type="button" id="add-interval-btn"
                                class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg transition-colors duration-200">
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
                            class="inline-flex items-center justify-center h-12 px-8 text-sm font-medium tracking-wide rounded-full bg-green-50 text-green-600 hover:bg-green-100 transition">
                            حفظ التعديلات
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const addIntervalBtn = document.getElementById('add-interval-btn');
            const intervalsContainer = document.getElementById('intervals-container');

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
                const existingIntervals = @json($intervals);
                existingIntervals.forEach(interval => {
                    createIntervalRow(interval.StartDate, interval.EndDate, interval.Price);
                });
            }

            ensureAtLeastOneInterval();
        });
    </script>
@endsection
