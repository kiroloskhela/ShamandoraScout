@extends('layouts.app', ['pageTitle' => 'حجز شخص في فعالية'])

@section('content')
    <div class="flex place-content-center">
        <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-xl border-2 border-blue-300" dir="rtl">
            <div class="mb-6 text-center">
                <h2 class="text-xl font-bold text-gray-800">اختيار الفعالية</h2>
            </div>

            <form method="GET" action="" onsubmit="return goToEvent();">
                <div class="space-y-6">
                    <div>
                        <label class="block mb-2 text-sm text-gray-700">اختر فعالية (المفعّلة مالياً)</label>
                        <select id="season_event_id"
                            class="w-full h-12 px-4 border rounded-lg text-right border-slate-200 text-slate-600 focus:border-blue-500 focus:outline-none"
                            required>
                            <option value="">-- اختر --</option>
                            @foreach ($events as $ev)
                                <option value="{{ $ev->SeasonEventID }}">
                                    {{ $ev->SeasonName }} ({{ $ev->SeasonYear }}) — {{ $ev->EventName }}
                                    | مدعوم: {{ $ev->SupportedPrice }} | أقصى: {{ $ev->ActualMaxPrice }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex justify-center">
                        <button type="submit"
                            class="inline-flex items-center justify-center h-12 px-8 text-sm font-medium rounded-full bg-blue-50 text-blue-600 hover:bg-blue-100 transition">
                            التالي
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        function goToEvent() {
            const id = document.getElementById('season_event_id').value;
            if (!id) return false;
            window.location.href = "{{ url('booking/event') }}/" + id;
            return false;
        }
    </script>
@endsection
