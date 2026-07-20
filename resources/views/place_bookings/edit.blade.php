@extends('layouts.app', ['pageTitle' => __('Edit place booking request')])

@section('content')
    <div class="container mx-auto px-4 py-8">

        <div class="mb-8 text-center">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">{{ __('Edit place booking request') }}</h1>
            <p class="text-gray-600">{{ __('You can edit the request only while it is pending review') }}</p>
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
                    <h2 class="text-lg font-bold text-gray-800">{{ __('1) Select place') }}</h2>
                    <span class="text-xs text-gray-500">{{ __('Location → Place') }}</span>
                </div>

                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <label class="block mb-2 text-sm text-gray-700">{{ __('Location') }}</label>
                        <select name="location_id" id="location_id"
                            class="w-full h-12 border rounded-lg px-4 text-right border-slate-200 text-slate-700 focus:border-green-500 focus:outline-none"
                            required>
                            <option value="" disabled>{{ __('Choose location') }}</option>
                            @foreach ($locations as $l)
                                <option value="{{ $l->LocationID }}"
                                    {{ (string) old('location_id', $booking->LocationID) === (string) $l->LocationID ? 'selected' : '' }}>
                                    {{ $l->LocationName }}
                                </option>
                            @endforeach
                        </select>
                        <p class="mt-2 text-xs text-gray-500">{{ __('Choose location first to show places under it.') }}</p>
                    </div>

                    <div>
                        <label class="block mb-2 text-sm text-gray-700">{{ __('Place') }}</label>
                        <select name="place_id" id="place_id"
                            class="w-full h-12 border rounded-lg px-4 text-right border-slate-200 text-slate-700 focus:border-green-500 focus:outline-none"
                            required>
                            <option value="" disabled>{{ __('Choose place') }}</option>
                            {{-- will be filled by JS --}}
                        </select>
                        <p class="mt-2 text-xs text-gray-500">{{ __('Places will load automatically after selecting a location.') }}</p>
                    </div>
                </div>
            </div>

            {{-- Optional dropdown --}}
            <div class="bg-white rounded-lg shadow-lg p-6 mb-6 border-2 border-blue-200">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-bold text-gray-800">{{ __('Additional info (optional)') }}</h2>
                    <span class="text-xs text-gray-500">{{ __('Sector') }}</span>
                </div>

                <div class="grid md:grid-cols-1 gap-6">
                    <div>
                        <label class="block mb-2 text-sm text-gray-700">{{ __('Sector') }}</label>
                        <select name="qetaa_id" id="qetaa_id"
                            class="w-full h-12 border rounded-lg px-4 text-right border-slate-200 text-slate-700 focus:border-blue-500 focus:outline-none">
                            <option value="">-- {{ __('None') }} --</option>
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
                    <h2 class="text-lg font-bold text-gray-800">{{ __('2) Date and time') }}</h2>
                    <span class="text-xs text-gray-500">{{ __('Update date + time') }}</span>
                </div>

                <div class="grid md:grid-cols-3 gap-6 items-end">
                    <div>
                        <label class="block mb-2 text-sm text-gray-700">{{ __('Date') }}</label>
                        <input type="date" name="booking_date" id="booking_date"
                            value="{{ old('booking_date', $booking->BookingDate) }}"
                            class="w-full h-12 border rounded-lg px-4 text-right border-slate-200 text-slate-700 focus:border-yellow-500 focus:outline-none"
                            required>
                    </div>

                    <div>
                        <label class="block mb-2 text-sm text-gray-700">{{ __('From') }}</label>
                        <input type="time" name="time_from" id="time_from"
                            value="{{ old('time_from', $booking->TimeFrom) }}"
                            class="w-full h-12 border rounded-lg px-4 text-right border-slate-200 text-slate-700 focus:border-yellow-500 focus:outline-none"
                            required>
                    </div>

                    <div>
                        <label class="block mb-2 text-sm text-gray-700">{{ __('To') }}</label>
                        <input type="time" name="time_to" id="time_to" value="{{ old('time_to', $booking->TimeTo) }}"
                            class="w-full h-12 border rounded-lg px-4 text-right border-slate-200 text-slate-700 focus:border-yellow-500 focus:outline-none"
                            required>
                    </div>
                </div>

                <p class="mt-3 text-xs text-gray-500">
                    {{ __('Note: Multiple requests can exist at the same time; there is no conflict blocking submission.') }}
                </p>
            </div>

            {{-- Note --}}
            <div class="bg-white rounded-lg shadow-lg p-6 mb-6 border-2 border-slate-200">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-bold text-gray-800">{{ __('Note (optional)') }}</h2>
                    <span class="text-xs text-gray-500">{{ __('Message to admin') }}</span>
                </div>

                <textarea name="user_note" id="user_note" rows="3"
                    class="w-full border rounded-lg p-3 text-right border-slate-200 text-slate-700 focus:border-blue-500 focus:outline-none"
                    placeholder="{{ __('Write any note...') }}">{{ old('user_note', $booking->UserNote) }}</textarea>
            </div>

            {{-- Actions --}}
            <div class="text-center">
                <button type="submit"
                    class="inline-flex items-center justify-center h-12 px-10 text-sm font-medium rounded-full
                       bg-green-50 text-green-700 hover:bg-green-100 transition border border-green-200">
                    {{ __('Save changes') }}
                </button>

                <a href="{{ route('place_bookings.show', $booking->BookingID) }}"
                    class="inline-flex items-center justify-center h-12 px-10 text-sm font-medium rounded-full
                       bg-gray-50 text-gray-700 hover:bg-gray-100 transition border border-gray-200 mr-2">{{ __('Back') }}</a>
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
                placeSelect.innerHTML = '<option value="" selected disabled>{{ __('Loading...') }}</option>';
                placeSelect.setAttribute('disabled', 'disabled');

                try {
                    const res = await fetch(`${ajaxBase}/${locationId}`);
                    const data = await res.json();

                    placeSelect.innerHTML = '<option value="" disabled>{{ __('Choose place') }}</option>';

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
                    alert(@json(__('Please select a place.')));
                    return;
                }
                if (timeFrom.value >= timeTo.value) {
                    e.preventDefault();
                    alert(@json(__('End time must be after start time.')));
                    return;
                }
            });
        });
    </script>
@endsection
