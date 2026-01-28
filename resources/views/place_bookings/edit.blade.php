@extends('layouts.app', ['pageTitle' => 'تعديل طلب حجز مكان'])

@section('content')
    <div class="container mx-auto px-4 py-8" dir="rtl">

        <div class="mb-8 text-center">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">تعديل طلب حجز مكان</h1>
            <p class="text-gray-600">يمكن تعديل الطلب طالما أنه قيد المراجعة فقط</p>
        </div>

        {{-- Alerts --}}
        @if ($errors->any())
            <div class="mb-6 p-3 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm">
                <ul class="list-disc pr-5 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li class="leading-5">{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        @if (session('error'))
            <div class="mb-6 p-3 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm">
                {{ session('error') }}
            </div>
        @endif

        <form id="bookingForm" method="POST" action="{{ route('place_bookings.update', $booking->BookingID) }}">
            @csrf
            @method('PATCH')

            {{-- Step 1: Location + Place --}}
            <div class="bg-white rounded-lg shadow-lg p-6 mb-6 border-2 border-green-300">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-bold text-gray-800">١) المكان</h2>
                    <span class="text-xs text-gray-500">الموقع → المكان</span>
                </div>

                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <label class="block mb-2 text-sm text-gray-700">الموقع</label>
                        <select name="location_id" id="location_id"
                            class="w-full h-12 border rounded-lg px-4 text-right border-slate-200 text-slate-700 focus:border-green-500 focus:outline-none"
                            required>
                            <option value="" disabled>اختر الموقع</option>
                            @foreach ($locations as $l)
                                <option value="{{ $l->LocationID }}"
                                    {{ (string) old('location_id', $booking->LocationID) === (string) $l->LocationID ? 'selected' : '' }}>
                                    {{ $l->LocationName }}
                                </option>
                            @endforeach
                        </select>
                        <p class="mt-2 text-xs text-gray-500">اختيار الموقع يحدد الأماكن المتاحة.</p>
                    </div>

                    <div>
                        <label class="block mb-2 text-sm text-gray-700">المكان</label>
                        <select name="place_id" id="place_id"
                            class="w-full h-12 border rounded-lg px-4 text-right border-slate-200 text-slate-700 focus:border-green-500 focus:outline-none"
                            required>
                            <option value="" disabled>اختر المكان</option>
                            {{-- will be filled by JS --}}
                        </select>
                        <p class="mt-2 text-xs text-gray-500">سيتم تحميل الأماكن تلقائيًا بعد اختيار الموقع.</p>
                    </div>
                </div>
            </div>

            {{-- Optional dropdown --}}
            <div class="bg-white rounded-lg shadow-lg p-6 mb-6 border-2 border-blue-200">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-bold text-gray-800">معلومات إضافية (اختياري)</h2>
                    <span class="text-xs text-gray-500">القطاع</span>
                </div>

                <div class="grid md:grid-cols-1 gap-6">
                    <div>
                        <label class="block mb-2 text-sm text-gray-700">القطاع</label>
                        <select name="qetaa_id" id="qetaa_id"
                            class="w-full h-12 border rounded-lg px-4 text-right border-slate-200 text-slate-700 focus:border-blue-500 focus:outline-none">
                            <option value="">-- بدون --</option>
                            @foreach ($qetaat as $q)
                                <option value="{{ $q->QetaaID }}"
                                    {{ (string) old('qetaa_id', $booking->QetaaID) === (string) $q->QetaaID ? 'selected' : '' }}>
                                    {{ $q->QetaaName }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            {{-- Step 2: Date + Time --}}
            <div class="bg-white rounded-lg shadow-lg p-6 mb-6 border-2 border-yellow-300">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-bold text-gray-800">٢) التاريخ والوقت</h2>
                    <span class="text-xs text-gray-500">تحديث التاريخ + الوقت</span>
                </div>

                <div class="grid md:grid-cols-3 gap-6 items-end">
                    <div>
                        <label class="block mb-2 text-sm text-gray-700">التاريخ</label>
                        <input type="date" name="booking_date" id="booking_date"
                            value="{{ old('booking_date', $booking->BookingDate) }}"
                            class="w-full h-12 border rounded-lg px-4 text-right border-slate-200 text-slate-700 focus:border-yellow-500 focus:outline-none"
                            required>
                    </div>

                    <div>
                        <label class="block mb-2 text-sm text-gray-700">من</label>
                        <input type="time" name="time_from" id="time_from"
                            value="{{ old('time_from', $booking->TimeFrom) }}"
                            class="w-full h-12 border rounded-lg px-4 text-right border-slate-200 text-slate-700 focus:border-yellow-500 focus:outline-none"
                            required>
                    </div>

                    <div>
                        <label class="block mb-2 text-sm text-gray-700">إلى</label>
                        <input type="time" name="time_to" id="time_to" value="{{ old('time_to', $booking->TimeTo) }}"
                            class="w-full h-12 border rounded-lg px-4 text-right border-slate-200 text-slate-700 focus:border-yellow-500 focus:outline-none"
                            required>
                    </div>
                </div>

                <p class="mt-3 text-xs text-gray-500">
                    ملاحظة: يمكن وجود أكثر من طلب لنفس الوقت، ولا يوجد تعارض يمنع الإرسال.
                </p>
            </div>

            {{-- Note --}}
            <div class="bg-white rounded-lg shadow-lg p-6 mb-6 border-2 border-slate-200">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-bold text-gray-800">ملاحظة (اختياري)</h2>
                    <span class="text-xs text-gray-500">رسالة للإدارة</span>
                </div>

                <textarea name="user_note" id="user_note" rows="3"
                    class="w-full border rounded-lg p-3 text-right border-slate-200 text-slate-700 focus:border-blue-500 focus:outline-none"
                    placeholder="اكتب أي ملاحظة...">{{ old('user_note', $booking->UserNote) }}</textarea>
            </div>

            {{-- Actions --}}
            <div class="text-center">
                <button type="submit"
                    class="inline-flex items-center justify-center h-12 px-10 text-sm font-medium rounded-full
                       bg-green-50 text-green-700 hover:bg-green-100 transition border border-green-200">
                    حفظ التعديلات
                </button>

                <a href="{{ route('place_bookings.show', $booking->BookingID) }}"
                    class="inline-flex items-center justify-center h-12 px-10 text-sm font-medium rounded-full
                       bg-gray-50 text-gray-700 hover:bg-gray-100 transition border border-gray-200 mr-2">
                    رجوع
                </a>
            </div>
        </form>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const locationSelect = document.getElementById('location_id');
            const placeSelect = document.getElementById('place_id');

            const oldPlace = "{{ old('place_id', $booking->PlaceID) }}";
            const ajaxBase = `{{ url('/ajax/places') }}`;

            async function loadPlaces(locationId, selectedPlaceId = null) {
                placeSelect.innerHTML = '<option value="" selected disabled>جاري التحميل...</option>';
                placeSelect.setAttribute('disabled', 'disabled');

                try {
                    const res = await fetch(`${ajaxBase}/${locationId}`);
                    const data = await res.json();

                    placeSelect.innerHTML = '<option value="" disabled>اختر المكان</option>';

                    if (!Array.isArray(data) || data.length === 0) {
                        placeSelect.innerHTML =
                            '<option value="" selected disabled>لا توجد أماكن لهذا الموقع</option>';
                        placeSelect.setAttribute('disabled', 'disabled');
                        return;
                    }

                    data.forEach(p => {
                        const opt = document.createElement('option');
                        opt.value = p.PlaceID;
                        opt.textContent = p.PlaceName;

                        if (selectedPlaceId && String(selectedPlaceId) === String(p.PlaceID)) {
                            opt.selected = true;
                        }

                        placeSelect.appendChild(opt);
                    });

                    placeSelect.removeAttribute('disabled');
                } catch (e) {
                    placeSelect.innerHTML =
                        '<option value="" selected disabled>تعذر تحميل الأماكن</option>';
                    placeSelect.setAttribute('disabled', 'disabled');
                }
            }

            // Initial load based on selected location
            if (locationSelect.value) {
                loadPlaces(locationSelect.value, oldPlace);
            }

            locationSelect.addEventListener('change', function() {
                if (!locationSelect.value) return;
                loadPlaces(locationSelect.value, null);
            });

            // Basic time validation (client side)
            const form = document.getElementById('bookingForm');
            const timeFrom = document.getElementById('time_from');
            const timeTo = document.getElementById('time_to');

            form.addEventListener('submit', function(e) {
                if (!placeSelect.value) {
                    e.preventDefault();
                    alert('من فضلك اختر المكان.');
                    return;
                }
                if (timeFrom.value >= timeTo.value) {
                    e.preventDefault();
                    alert('وقت (إلى) يجب أن يكون بعد وقت (من).');
                    return;
                }
            });
        });
    </script>
@endsection
