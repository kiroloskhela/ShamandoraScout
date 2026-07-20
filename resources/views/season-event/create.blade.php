@extends('layouts.app', ['pageTitle' => __('Link season to event')])

@section('content')
    <div class="flex place-content-center">
        <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-md border-2 border-blue-300">
            <div class="mb-6 text-center">
                <h2 class="text-xl font-bold text-gray-800">ربط موسم بفعالية</h2>
            </div>

            @if ($errors->any())
                <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-3 text-red-700 text-sm">
                    <ul class="list-disc pr-5 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('season-event.insert') }}">
                @csrf
                <div class="space-y-6">

                    <!-- Season -->
                    <div>
                        <label for="season_id" class="block mb-2 text-sm text-gray-700">{{ __('Choose season') }}</label>
                        <select id="season_id" name="season_id" required
                            class="w-full h-12 px-4 border rounded-lg text-right border-slate-200 text-slate-600 focus:border-blue-500 focus:outline-none">
                            <option value="">-- اختر الموسم --</option>
                            @foreach ($seasons as $season)
                                <option value="{{ $season->SeasonID }}" @selected(old('season_id') == $season->SeasonID)>
                                    {{ $season->SeasonName }} ({{ $season->SeasonYear }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Events (multiple) -->
                    <div>
                        <label for="event_id" class="block mb-2 text-sm text-gray-700">اختر الفعاليات</label>
                        <select id="event_id" name="event_id[]" multiple required
                            class="w-full h-40 px-4 border rounded-lg text-right border-slate-200 text-slate-600 focus:border-blue-500 focus:outline-none">
                            @foreach ($events as $event)
                                <option value="{{ $event->EventID }}" @if (collect(old('event_id', []))->contains($event->EventID)) selected @endif>
                                    {{ $event->EventName }}
                                    ({{ \Carbon\Carbon::parse($event->EventStartDate)->format('Y-m-d') }} →
                                    {{ \Carbon\Carbon::parse($event->EventEndDate)->format('Y-m-d') }})
                                </option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-500 mt-1">اضغط Ctrl/Command + Click لاختيار أكثر من فعالية</p>
                    </div>

                    <!-- Submit -->
                    <div class="flex justify-center">
                        <button type="submit"
                            class="inline-flex items-center justify-center h-12 px-8 text-sm font-medium tracking-wide rounded-full bg-blue-600 text-white hover:bg-blue-700 transition">
                            ربط
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
