@extends('layouts.app' , ['pageTitle' => __('Edit season-event link')])

@section('content')
<div class="flex place-content-center">
    <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-md border-2 border-green-300">
        <div class="mb-6 text-center">
            <h2 class="text-xl font-bold text-gray-800">تعديل الربط</h2>
        </div>

        <form method="POST" action="{{ route('season-event.update', $seasonEvent->SeasonEventID) }}">
            @csrf
            @method('PATCH')
            <div class="space-y-6">
                <!-- Select Season -->
                <div class="relative">
                    <label for="season_id" class="block mb-2 text-sm text-gray-700">{{ __('Choose season') }}</label>
                    <select id="season_id" name="season_id" required
                        class="w-full h-12 ps-4 border rounded-lg border-slate-200 text-slate-600 focus:border-green-500 focus:outline-none">
                        @foreach($seasons as $season)
                            <option value="{{ $season->SeasonID }}" 
                                {{ $season->SeasonID == $seasonEvent->SeasonID ? 'selected' : '' }}>
                                {{ $season->SeasonName }} ({{ $season->SeasonYear }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Select Event -->
                <div class="relative">
                    <label for="event_id" class="block mb-2 text-sm text-gray-700">{{ __('Choose event') }}</label>
                    <select id="event_id" name="event_id" required
                        class="w-full h-12 ps-4 border rounded-lg border-slate-200 text-slate-600 focus:border-green-500 focus:outline-none">
                        @foreach($events as $event)
                            <option value="{{ $event->EventID }}" 
                                {{ $event->EventID == $seasonEvent->EventID ? 'selected' : '' }}>
                                {{ $event->EventName }}
                                ({{ \Carbon\Carbon::parse($event->EventStartDate)->format('Y-m-d') }} → 
                                 {{ \Carbon\Carbon::parse($event->EventEndDate)->format('Y-m-d') }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Submit -->
                <div class="flex justify-center">
                    <button type="submit"
                        class="inline-flex items-center justify-center h-12 px-8 text-sm font-medium tracking-wide rounded-full bg-green-50 text-green-600 hover:bg-green-100 hover:text-green-700 transition">
                        حفظ التعديلات
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
