@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <h2>حذف حضور</h2>

        <div class="alert alert-danger">
            هل أنت متأكد أنك تريد حذف هذا السجل؟
        </div>

        <form action="{{ route('attendance.destroy', $attendance->AttendanceID) }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-danger">نعم، حذف</button>
            <a href="{{ route('attendance.index') }}" class="btn btn-secondary">إلغاء</a>
        </form>
    </div>
@endsection
