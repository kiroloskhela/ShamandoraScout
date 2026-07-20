@extends('layouts.app', ['pageTitle' => __('Place booking request')])

@section('content')
    <div class="container mx-auto px-4 py-8">

        {{-- Header --}}
        <div class="mb-8 text-center">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">{{ __('Place booking request') }}</h1>
            <p class="text-gray-600">{{ __('Select location then place then date and time and submit') }}</p>
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

        {{-- Step 1: Location + Place --}}
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6 border-2 border-green-300">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-bold text-gray-800">{{ __('1) Select place') }}</h2>
                <span class="text-xs text-gray-500">{{ __('Location → Place') }}</span>
            </div>

            <div class="grid md:grid-cols-2 gap-6">
                <div>
                    <label class="block mb-2 text-sm text-gray-700">{{ __('Location') }}</label>
                    <select id="location_id"
                        class="w-full h-12 border rounded-lg px-4 text-right border-slate-200 text-slate-700 focus:border-green-500 focus:outline-none">
                        <option value="" selected disabled>{{ __('Choose location') }}</option>
                        @foreach ($locations as $l)
                            <option value="{{ $l->LocationID }}"
                                {{ old('location_id') == $l->LocationID ? 'selected' : '' }}>
                                {{ $l->LocationName }}
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-2 text-xs text-gray-500">{{ __('Choose location first to show places under it.') }}</p>
                </div>

                <div>
                    <label class="block mb-2 text-sm text-gray-700">{{ __('Place') }}</label>
                    <select id="place_id"
                        class="w-full h-12 border rounded-lg px-4 text-right border-slate-200 text-slate-700 focus:border-green-500 focus:outline-none"
                        disabled>
                        <option value="" selected disabled>{{ __('Choose place') }}</option>
                    </select>
                    <p class="mt-2 text-xs text-gray-500">{{ __('Places will load automatically after selecting a location.') }}</p>
                </div>
            </div>
        </div>

        {{-- Optional Info --}}
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6 border-2 border-blue-200">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-bold text-gray-800">{{ __('Additional info (optional)') }}</h2>
                <span class="text-xs text-gray-500">{{ __('Sector') }}</span>
            </div>

            <div class="grid md:grid-cols-1 gap-6">
                <div>
                    <label class="block mb-2 text-sm text-gray-700">{{ __('Sector') }}</label>
                    <select id="qetaa_id"
                        class="w-full h-12 border rounded-lg px-4 text-right border-slate-200 text-slate-700 focus:border-blue-500 focus:outline-none">
                        <option value="">-- {{ __('None') }} --</option>
                        @foreach ($qetaat as $q)
                            <option value="{{ $q->QetaaID }}" {{ old('qetaa_id') == $q->QetaaID ? 'selected' : '' }}>
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
                <h2 class="text-lg font-bold text-gray-800">{{ __('2) Date and time') }}</h2>
                <span class="text-xs text-gray-500">{{ __('Date + from/to') }}</span>
            </div>

            <div class="grid md:grid-cols-3 gap-6 items-end">
                <div>
                    <label class="block mb-2 text-sm text-gray-700">{{ __('Date') }}</label>
                    <input type="date" id="booking_date" value="{{ old('booking_date') }}"
                        class="w-full h-12 border rounded-lg px-4 text-right border-slate-200 text-slate-700 focus:border-yellow-500 focus:outline-none">
                </div>

                <div>
                    <label class="block mb-2 text-sm text-gray-700">{{ __('From') }}</label>
                    <input type="time" id="time_from" value="{{ old('time_from') }}"
                        class="w-full h-12 border rounded-lg px-4 text-right border-slate-200 text-slate-700 focus:border-yellow-500 focus:outline-none">
                </div>

                <div>
                    <label class="block mb-2 text-sm text-gray-700">{{ __('To') }}</label>
                    <input type="time" id="time_to" value="{{ old('time_to') }}"
                        class="w-full h-12 border rounded-lg px-4 text-right border-slate-200 text-slate-700 focus:border-yellow-500 focus:outline-none">
                </div>
            </div>

            <p class="mt-3 text-xs text-gray-500">
                {{ __('Note: Multiple requests can exist at the same time; admin will assign places on approval if needed.') }}
            </p>
        </div>

        {{-- Note --}}
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6 border-2 border-slate-200">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-bold text-gray-800">{{ __('Note (optional)') }}</h2>
                <span class="text-xs text-gray-500">{{ __('Message to admin') }}</span>
            </div>

            <textarea id="user_note" rows="3"
                class="w-full border rounded-lg p-3 text-right border-slate-200 text-slate-700 focus:border-blue-500 focus:outline-none"
                placeholder="{{ __('Write any note...') }}">{{ old('user_note') }}</textarea>
        </div>

        {{-- Submit --}}
        <div class="text-center">
            <form id="bookingForm" method="POST" action="{{ route('place_bookings.store') }}">
                @csrf

                <input type="hidden" name="location_id" id="hidden_location_id">
                <input type="hidden" name="place_id" id="hidden_place_id">
                <input type="hidden" name="qetaa_id" id="hidden_qetaa_id">

                <input type="hidden" name="booking_date" id="hidden_booking_date">
                <input type="hidden" name="time_from" id="hidden_time_from">
                <input type="hidden" name="time_to" id="hidden_time_to">

                <input type="hidden" name="user_note" id="hidden_user_note">

                <button type="submit"
                    class="inline-flex items-center justify-center h-12 px-10 text-sm font-medium rounded-full
                       bg-green-50 text-green-700 hover:bg-green-100 transition border border-green-200">
                    {{ __('Send request') }}
                </button>

                <p class="mt-3 text-xs text-gray-500">
                    {{ __('The request will be submitted with status') }} <span class="font-bold">{{ __('Pending review') }}</span>.
                </p>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const locationSelect = document.getElementById('location_id');
            const placeSelect = document.getElementById('place_id');

            const bookingDate = document.getElementById('booking_date');
            const timeFrom = document.getElementById('time_from');
            const timeTo = document.getElementById('time_to');

            const qetaaSelect = document.getElementById('qetaa_id');
            const userNote = document.getElementById('user_note');

            const form = document.getElementById('bookingForm');

            const hiddenLocationId = document.getElementById('hidden_location_id');
            const hiddenPlaceId = document.getElementById('hidden_place_id');
            const hiddenQetaaId = document.getElementById('hidden_qetaa_id');

            const hiddenBookingDate = document.getElementById('hidden_booking_date');
            const hiddenTimeFrom = document.getElementById('hidden_time_from');
            const hiddenTimeTo = document.getElementById('hidden_time_to');

            const hiddenUserNote = document.getElementById('hidden_user_note');

            function escapeHtml(str) {
                return String(str ?? '').replace(/[&<>"']/g, s => ({
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#39;'
                } [s]));
            }

            async function loadPlaces(locationId, selectedPlaceId = null) {
                placeSelect.innerHTML = '<option value="" selected disabled>{{ __('Loading...') }}</option>';
                placeSelect.setAttribute('disabled', 'disabled');

                try {
                    const res = await fetch(`{{ url('/ajax/places') }}/${locationId}`);
                    const data = await res.json();

                    placeSelect.innerHTML = '<option value="" selected disabled>{{ __('Choose place') }}</option>';

                    if (!Array.isArray(data) || data.length === 0) {
                        placeSelect.innerHTML =
                            '<option value="" selected disabled>' + @json(__('No places for this location')) + '</option>';
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
                        '<option value="" selected disabled>' + @json(__('Failed to load places')) + '</option>';
                    placeSelect.setAttribute('disabled', 'disabled');
                }
            }

            // If old location exists (after validation error), load places
            const oldLocation = "{{ old('location_id') }}";
            const oldPlace = "{{ old('place_id') }}";
            if (oldLocation) {
                loadPlaces(oldLocation, oldPlace);
            }

            locationSelect.addEventListener('change', function() {
                const locId = locationSelect.value;
                if (!locId) return;
                loadPlaces(locId, null);
            });

            form.addEventListener('submit', function(e) {
                if (!locationSelect.value) {
                    e.preventDefault();
                    alert(@json(__('Please select a location.')));
                    return;
                }
                if (!placeSelect.value) {
                    e.preventDefault();
                    alert(@json(__('Please select a place.')));
                    return;
                }
                if (!bookingDate.value) {
                    e.preventDefault();
                    alert(@json(__('Please select a date.')));
                    return;
                }
                if (!timeFrom.value || !timeTo.value) {
                    e.preventDefault();
                    alert(@json(__('Please select time (from / to).')));
                    return;
                }
                if (timeFrom.value >= timeTo.value) {
                    e.preventDefault();
                    alert(@json(__('End time must be after start time.')));
                    return;
                }

                hiddenLocationId.value = locationSelect.value;
                hiddenPlaceId.value = placeSelect.value;
                hiddenQetaaId.value = qetaaSelect.value || '';

                hiddenBookingDate.value = bookingDate.value;
                hiddenTimeFrom.value = timeFrom.value;
                hiddenTimeTo.value = timeTo.value;

                hiddenUserNote.value = userNote.value || '';
            });
        });
    </script>
@endsection
