@extends('layouts.app' , ['pageTitle' => "ربط موسم بفعالية"])

@section('content')
<div class="flex place-content-center">
    <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-md border-2 border-blue-300" dir="rtl">
        <!-- Title -->
        <div class="mb-6 text-center">
            <h2 class="text-xl font-bold text-gray-800">ربط موسم بفعالية</h2>
        </div>

        <form method="POST" action="{{ route('season-event.insert') }}">
            @csrf
            <div class="space-y-6">
                <!-- Select Season -->
                <div class="relative">
                    <label for="season_id" class="block mb-2 text-sm text-gray-700">اختر الموسم</label>
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
                    <label for="event_id" class="block mb-2 text-sm text-gray-700">اختر الفعالية</label>
                    <select id="event_id" name="event_id" required
                        class="w-full h-12 px-4 border rounded-lg text-right border-slate-200 text-slate-600 focus:border-blue-500 focus:outline-none">
                        <option value="">-- اختر الفعالية --</option>
                        @foreach($events as $event)
                            <option value="{{ $event->EventID }}">
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
                        class="inline-flex items-center justify-center h-12 px-8 text-sm font-medium tracking-wide rounded-full bg-blue-50 text-blue-500 hover:bg-blue-100 hover:text-blue-600 transition">
                        ربط
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
