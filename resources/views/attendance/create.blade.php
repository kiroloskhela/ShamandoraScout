@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <h2>إضافة حضور جديد</h2>

        <form action="{{ route('attendance.insert') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label class="form-label">الحدث</label>
                <select name="SeasonEventID" class="form-control" required>
                    <option value="">-- اختر حدث --</option>
                    @foreach ($events as $event)
                        <option value="{{ $event->SeasonEventID }}">
                            {{ $event->EventName }} ({{ $event->SeasonName }} {{ $event->SeasonYear }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">الشخص (Served)</label>
                <select name="ServedID" class="form-control" required>
                    <option value="">-- اختر شخص --</option>
                    @foreach ($people as $p)
                        <option value="{{ $p->PersonID }}">{{ $p->FirstName }} {{ $p->SecondName }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">أخذ الحضور بواسطة (Servent)</label>
                <select name="ServentID" class="form-control" required>
                    <option value="">-- اختر شخص --</option>
                    @foreach ($people as $p)
                        <option value="{{ $p->PersonID }}">{{ $p->FirstName }} {{ $p->SecondName }}</option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="btn btn-success">حفظ</button>
            <a href="{{ route('attendance.index') }}" class="btn btn-secondary">إلغاء</a>
        </form>
    </div>
@endsection
