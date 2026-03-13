@extends('layouts.app', ['pageTitle' => 'اختيار الفعالية لإدارة الحجوزات المالية'])

@section('content')
    <div class="flex place-content-center">
        <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-md border-2 border-blue-300" dir="rtl">
            <div class="mb-6 text-center">
                <h2 class="text-xl font-bold text-gray-800">اختيار الفعالية</h2>
            </div>

            @if ($errors->has('general'))
                <div class="mb-4 rounded-lg bg-red-100 border border-red-300 text-red-800 px-4 py-3">
                    {{ $errors->first('general') }}
                </div>
            @endif

            <div class="space-y-6">
                <div>
                    <label for="season_id" class="block mb-2 text-sm text-gray-700">اختر الموسم</label>
                    <select id="season_id"
                        class="w-full h-12 px-4 border rounded-lg text-right border-slate-200 text-slate-600 focus:border-blue-500 focus:outline-none">
                        <option value="">-- اختر الموسم --</option>
                        @foreach ($seasons as $season)
                            <option value="{{ $season->SeasonID }}">{{ $season->SeasonName }} ({{ $season->SeasonYear }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="season_event_id" class="block mb-2 text-sm text-gray-700">اختر الفعالية</label>
                    <select id="season_event_id"
                        class="w-full h-12 px-4 border rounded-lg text-right border-slate-200 text-slate-600 focus:border-blue-500 focus:outline-none">
                        <option value="">-- اختر الفعالية --</option>
                    </select>
                </div>

                <div class="flex justify-center">
                    <button type="button" id="go-btn"
                        class="inline-flex items-center justify-center h-12 px-8 text-sm font-medium tracking-wide rounded-full bg-blue-50 text-blue-500 hover:bg-blue-100 hover:text-blue-600 transition">
                        دخول
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const seasonSelect = document.getElementById('season_id');
            const eventSelect = document.getElementById('season_event_id');
            const goBtn = document.getElementById('go-btn');

            seasonSelect.addEventListener('change', function() {
                const seasonId = this.value;
                eventSelect.innerHTML = '<option value="">-- اختر الفعالية --</option>';

                if (!seasonId) return;

                eventSelect.innerHTML = '<option value="">جاري التحميل...</option>';

                fetch(`{{ route('eventBookingFinance.getEventsWithPlan') }}?seasonID=${seasonId}`)
                    .then(res => res.json())
                    .then(events => {
                        eventSelect.innerHTML = '<option value="">-- اختر الفعالية --</option>';
                        events.forEach(event => {
                            const option = document.createElement('option');
                            option.value = event.SeasonEventID;
                            option.textContent =
                                `${event.EventTypeName} - ${event.EventName} (${event.EventStartDate} → ${event.EventEndDate})`;
                            eventSelect.appendChild(option);
                        });
                    })
                    .catch(() => {
                        eventSelect.innerHTML = '<option value="">خطأ في تحميل الفعاليات</option>';
                    });
            });

            goBtn.addEventListener('click', function() {
                const seasonEventID = eventSelect.value;
                if (!seasonEventID) {
                    alert('اختر الفعالية أولاً');
                    return;
                }
                window.location.href = `{{ url('event-booking-finance/event') }}/${seasonEventID}`;
            });
        });
    </script>
@endsection
