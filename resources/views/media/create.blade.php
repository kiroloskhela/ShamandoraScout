@extends('layouts.app' , ['pageTitle' => "ربط موسم بفعالية"])

@section('content')
<div class="flex place-content-center">
    <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-md border-2 border-blue-300" dir="rtl">
        <!-- Title -->
        <div class="mb-6 text-center">
            <h2 class="text-xl font-bold text-gray-800">ربط موسم بفعالية</h2>
        </div>

        <form method="POST" action="{{ route('media.insert') }}">
            @csrf
            <div class="space-y-6">
                <!-- Select Season -->
                <div class="relative">
                    <label for="season_id" class="block mb-2 text-sm text-gray-700">{{ __('Choose season') }}</label>
                    <select id="season_id" name="season_id" required
                        class="w-full h-12 px-4 border rounded-lg text-right border-slate-200 text-slate-600 focus:border-blue-500 focus:outline-none">
                        <option value="">-- اختر الموسم --</option>
                        @foreach($seasons as $season)
                            <option value="{{ $season->SeasonID }}">
                                {{ $season->SeasonName }} ({{ $season->SeasonYear }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Select Event -->
                <div class="relative">
                    <label for="season_event_id" class="block mb-2 text-sm text-gray-700">{{ __('Choose event') }}</label>
                    <select id="season_event_id" name="season_event_id" required
                        class="w-full h-12 px-4 border rounded-lg text-right border-slate-200 text-slate-600 focus:border-blue-500 focus:outline-none">
                        <option value="">-- اختر الفعالية --</option>
                    </select>
                </div>

                <!-- Drive Link Input -->
                <div class="relative">
                    <label for="drive_link" class="block mb-2 text-sm text-gray-700">رابط Drive</label>
                    <input type="url" id="drive_link" name="drive_link" required
                        class="w-full h-12 px-4 border rounded-lg text-right border-slate-200 text-slate-600 focus:border-blue-500 focus:outline-none"
                        placeholder="https://drive.google.com/...">
                </div>

                <!-- Submit -->
                <div class="flex justify-center">
                    <button type="submit"
                        class="inline-flex items-center justify-center h-12 px-8 text-sm font-medium tracking-wide rounded-full bg-blue-50 text-blue-500 hover:bg-blue-100 hover:text-blue-600 transition">
                        إضافة رابط
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
        const seasonId = this.value;
        
        // Clear events dropdown
        eventSelect.innerHTML = '<option value="">-- اختر الفعالية --</option>';
        
        if (seasonId) {
            // Show loading
            eventSelect.innerHTML = '<option value="">{{ __('Loading...') }}</option>';
            
            // Fetch events for selected season
            fetch(`{{ route('media.getEventsForSeason') }}?seasonID=${seasonId}`)
                .then(response => response.json())
                .then(events => {
                    eventSelect.innerHTML = '<option value="">-- اختر الفعالية --</option>';
                    
                    events.forEach(event => {
                        const option = document.createElement('option');
                        option.value = event.SeasonEventID;
                        option.textContent = `${event.EventName} (${event.EventStartDate} → ${event.EventEndDate})`;
                        eventSelect.appendChild(option);
                    });
                })
                .catch(error => {
                    console.error('Error fetching events:', error);
                    eventSelect.innerHTML = '<option value="">{{ __('Error loading events') }}</option>';
                });
        }
    });
});
</script>
@endsection