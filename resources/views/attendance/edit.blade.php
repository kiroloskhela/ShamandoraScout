@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <h2>تعديل حضور</h2>

        <form action="{{ route('attendance.update', $attendance->AttendanceID) }}" method="POST">
            @csrf

            <div class="mb-3">
                <label class="form-label">الحدث</label>
                <select name="SeasonEventID" class="form-control" required>
                    @foreach ($events as $event)
                        <option value="{{ $event->SeasonEventID }}"
                            {{ $event->SeasonEventID == $attendance->SeasonEventID ? 'selected' : '' }}>
                            {{ $event->EventName }} ({{ $event->SeasonName }} {{ $event->SeasonYear }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">الشخص (Served)</label>
                <select name="ServedID" class="form-control" required>
                    @foreach ($people as $p)
                        <option value="{{ $p->PersonID }}" {{ $p->PersonID == $attendance->ServedID ? 'selected' : '' }}>
                            {{ $p->FirstName }} {{ $p->SecondName }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">أخذ الحضور بواسطة (Servent)</label>
                <select name="ServentID" class="form-control" required>
                    @foreach ($people as $p)
                        <option value="{{ $p->PersonID }}"
                            {{ $p->PersonID == $attendance->ServentID ? 'selected' : '' }}>
                            {{ $p->FirstName }} {{ $p->SecondName }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="btn btn-success">تحديث</button>
            <a href="{{ route('attendance.index') }}" class="btn btn-secondary">إلغاء</a>
        </form>
    </div>
@endsection
