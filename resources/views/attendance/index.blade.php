@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <h2 class="mb-3">{{ $title }}</h2>

        <a href="{{ route('attendance.create') }}" class="btn btn-primary mb-3">+ إضافة حضور</a>

        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>الحدث</th>
                    <th>الموسم</th>
                    <th>الشخص</th>
                    <th>أخذ الحضور بواسطة</th>
                    <th>التاريخ</th>
                    <th>إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($attendances as $row)
                    <tr>
                        <td>{{ $row->AttendanceID }}</td>
                        <td>{{ $row->EventName }}</td>
                        <td>{{ $row->SeasonName }}</td>
                        <td>{{ $row->ServedFullName }}</td>
                        <td>{{ $row->TakenByFullName }}</td>
                        <td>{{ $row->TimeStamp }}</td>
                        <td>
                            <a href="{{ route('attendance.edit', $row->AttendanceID) }}"
                                class="btn btn-sm btn-warning">تعديل</a>
                            <a href="{{ route('attendance.delete', $row->AttendanceID) }}"
                                class="btn btn-sm btn-danger">حذف</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">لا توجد سجلات حضور</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
