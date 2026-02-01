@extends('layouts.app', ['pageTitle' => 'إضافة إعداد مالي لفعالية'])

@section('content')
    <div class="flex place-content-center">
        <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-md border-2 border-blue-300" dir="rtl">

            <!-- Title -->
            <div class="mb-6 text-center">
                <h2 class="text-xl font-bold text-gray-800">
                    إضافة إعداد مالي لفعالية
                </h2>
            </div>

            <form method="POST" action="{{ route('seasonEventFinance.insert') }}">
                @csrf

                <div class="space-y-6">

                    <!-- Select Season -->
                    <div>
                        <label class="block mb-2 text-sm text-gray-700">
                            اختر الموسم
                        </label>
                        <select id="season_id"
                            class="w-full h-12 px-4 border rounded-lg text-right border-slate-200 text-slate-600
                                   focus:border-blue-500 focus:outline-none"
                            required>
                            <option value="">-- اختر الموسم --</option>
                            @foreach ($seasons as $season)
                                <option value="{{ $season->SeasonID }}">
                                    {{ $season->SeasonName }} ({{ $season->SeasonYear }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Select Event -->
                    <div>
                        <label class="block mb-2 text-sm text-gray-700">
                            اختر الفعالية
                        </label>
                        <select id="season_event_id" name="season_event_id"
                            class="w-full h-12 px-4 border rounded-lg text-right border-slate-200 text-slate-600
                                   focus:border-blue-500 focus:outline-none"
                            required>
                            <option value="">-- اختر الفعالية --</option>
                        </select>
                    </div>

                    <!-- Supported Price -->
                    <div>
                        <label class="block mb-2 text-sm text-gray-700">
                            السعر المدعوم
                        </label>
                        <input type="number" step="0.01" name="supported_price" required
                            class="w-full h-12 px-4 border rounded-lg text-right border-slate-200 text-slate-600
                                  focus:border-blue-500 focus:outline-none"
                            placeholder="مثال: 1000">
                    </div>

                    <!-- Actual Max Price -->
                    <div>
                        <label class="block mb-2 text-sm text-gray-700">
                            السعر الفعلي
                        </label>
                        <input type="number" step="0.01" name="actual_max_price" required
                            class="w-full h-12 px-4 border rounded-lg text-right border-slate-200 text-slate-600
                                  focus:border-blue-500 focus:outline-none"
                            placeholder="مثال: 1500">
                    </div>

                    <!-- Installments Number -->
                    <div>
                        <label class="block mb-2 text-sm text-gray-700">
                            عدد الأقساط
                        </label>
                        <input type="number" name="installments_number" required min="1"
                            class="w-full h-12 px-4 border rounded-lg text-right border-slate-200 text-slate-600
                                  focus:border-blue-500 focus:outline-none"
                            placeholder="مثال: 5">
                    </div>

                    <!-- Submit -->
                    <div class="flex justify-center">
                        <button type="submit"
                            class="inline-flex items-center justify-center h-12 px-8 text-sm font-medium tracking-wide
                                   rounded-full bg-blue-50 text-blue-600 hover:bg-blue-100 hover:text-blue-700
                                   transition">
                            حفظ الإعدادات
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

            seasonSelect.addEventListener('change', function() {

                const seasonID = this.value;
                eventSelect.innerHTML = '<option value="">جاري التحميل...</option>';

                if (!seasonID) {
                    eventSelect.innerHTML = '<option value="">-- اختر الفعالية --</option>';
                    return;
                }

                fetch(`{{ route('seasonEventFinance.getEventsForSeason') }}?seasonID=${seasonID}`)
                    .then(response => response.json())
                    .then(data => {

                        eventSelect.innerHTML = '<option value="">-- اختر الفعالية --</option>';

                        data.forEach(event => {
                            const option = document.createElement('option');
                            option.value = event.SeasonEventID;
                            option.textContent =
                                `${event.EventName} (${event.EventStartDate} → ${event.EventEndDate})`;
                            eventSelect.appendChild(option);
                        });
                    })
                    .catch(() => {
                        eventSelect.innerHTML =
                            '<option value="">حدث خطأ أثناء تحميل الفعاليات</option>';
                    });
            });

        });
    </script>
@endsection
