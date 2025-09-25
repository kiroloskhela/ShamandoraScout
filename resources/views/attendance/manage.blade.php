@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-8" dir="rtl">

        <!-- Header -->
        <div class="mb-8 text-center">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">تسجيل الحضور</h1>
            <p class="text-gray-600">اختر الموسم والفعالية المصرح لك بها وسجّل حضور أفراد مجموعاتك فقط</p>
        </div>

        @if (session('success'))
            <div class="mb-6 p-4 rounded-lg bg-green-100 text-green-800 text-center font-semibold shadow">
                {{ session('success') }}
            </div>
        @endif

        <!-- Selection Card -->
        <form method="GET" action="{{ route('attendance.manage') }}"
            class="bg-white rounded-lg shadow-lg p-6 mb-8 border-2 border-blue-300">
            <div class="grid md:grid-cols-2 gap-6">
                <!-- Season -->
                <div class="relative">
                    <label for="season_id" class="block mb-2 text-sm font-semibold text-gray-700">اختر الموسم</label>
                    <select id="season_id" name="season_id"
                        class="w-full h-12 px-4 border rounded-lg text-right border-slate-200 text-slate-600 focus:border-blue-500 focus:outline-none"
                        onchange="this.form.submit()">
                        <option value="">-- اختر الموسم --</option>
                        @foreach ($seasons as $s)
                            <option value="{{ $s->SeasonID }}" {{ ($seasonId ?? null) == $s->SeasonID ? 'selected' : '' }}>
                                {{ $s->SeasonName }} ({{ $s->SeasonYear }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Event (only events that overlap with MY groups) -->
                <div class="relative">
                    <label for="season_event_id" class="block mb-2 text-sm font-semibold text-gray-700">اختر
                        الفعالية</label>
                    <select id="season_event_id" name="season_event_id"
                        class="w-full h-12 px-4 border rounded-lg text-right border-slate-200 {{ !empty($seasonId) ? 'text-slate-600' : 'text-slate-400' }} focus:border-blue-500 focus:outline-none"
                        {{ !empty($seasonId) ? '' : 'disabled' }} onchange="this.form.submit()">
                        <option value="">-- اختر الفعالية --</option>
                        @foreach ($events as $e)
                            <option value="{{ $e->SeasonEventID }}"
                                {{ ($seasonEventId ?? null) == $e->SeasonEventID ? 'selected' : '' }}>
                                {{ $e->EventName }} - {{ $e->EventStartDate }}
                            </option>
                        @endforeach
                    </select>
                    @if (($seasonId ?? null) && $events->isEmpty())
                        <p class="mt-2 text-xs text-amber-600">لا توجد فعاليات في هذا الموسم تخص مجموعاتك.</p>
                    @endif
                </div>
            </div>
        </form>

        <!-- Attendance Card -->
        @if (!empty($seasonEventId))
            @if (($persons->count() ?? 0) > 0)
                <div class="bg-white rounded-lg shadow-lg p-6 border-2 border-green-300">
                    <form method="POST" action="{{ route('attendance.save', $seasonEventId) }}">
                        @csrf
                        <input type="hidden" name="season_id" value="{{ $seasonId }}">

                        <!-- Servent: auto current user (read-only pill) -->
                        <div class="mb-6">
                            <div
                                class="flex items-center justify-between bg-slate-50 border border-slate-200 rounded-lg p-4">
                                <div class="text-sm text-slate-700 font-semibold">أخذ الحضور بواسطة</div>
                                <div class="text-sm text-slate-800">
                                    @php
                                        $first = optional($me)->FirstName ?? '';
                                        $second = optional($me)->SecondName ?? '';
                                        $third = optional($me)->ThirdName ?? '';
                                        $fourth = optional($me)->FourthName ?? '';
                                        $fullName = trim("$first $second $third $fourth");
                                    @endphp
                                    <span class="inline-block px-3 py-1 rounded-full bg-blue-100 text-blue-700">
                                        {{ $fullName ?: 'أنا' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Persons Table (only my groups within this event) -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full border border-slate-200 rounded-lg shadow-sm">
                                <thead class="bg-slate-100">
                                    <tr>
                                        <th class="px-4 py-2 text-sm font-semibold text-gray-700 text-center">حضر؟</th>
                                        <th class="px-4 py-2 text-sm font-semibold text-gray-700 text-right">الاسم</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($persons as $person)
                                        <tr class="border-t">
                                            <td class="px-4 py-2 text-center">
                                                <input type="checkbox" name="ServedIDs[]" value="{{ $person->PersonID }}"
                                                    class="h-5 w-5 text-green-600 border-gray-300 rounded focus:ring focus:ring-green-300"
                                                    {{ in_array($person->PersonID, $attendance) ? 'checked' : '' }}>
                                            </td>
                                            <td class="px-4 py-2 text-right">
                                                {{ $person->FirstName }} {{ $person->SecondName }}
                                                {{ $person->ThirdName }} {{ $person->FourthName }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="flex justify-center mt-6">
                            <button type="submit"
                                class="inline-flex items-center justify-center h-12 px-8 text-sm font-medium tracking-wide rounded-full bg-green-500 text-white hover:bg-green-600 transition">
                                💾 حفظ الحضور
                            </button>
                        </div>
                    </form>
                </div>
            @else
                <div class="bg-white rounded-lg shadow p-6 border border-slate-200 text-center text-slate-600">
                    لا يوجد أفراد من مجموعاتك مشاركين في هذه الفعالية.
                </div>
            @endif
        @endif

    </div>
@endsection
