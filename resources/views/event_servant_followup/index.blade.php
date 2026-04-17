@extends('layouts.app')

@section('content')
    <div class="container">
        <h3 class="mb-3">متابعة حجز المخدومين</h3>

        <div class="card mb-4">
            <div class="card-body">
                <strong>الموسم:</strong> {{ $event->SeasonName }} - {{ $event->SeasonYear }}<br>
                <strong>الفعالية:</strong> {{ $event->EventTypeName }} - {{ $event->EventName }}<br>
                <strong>التاريخ:</strong> {{ $event->EventStartDate }} إلى {{ $event->EventEndDate }}
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">المحجوزين</div>
            <div class="card-body p-0">
                <table class="table table-bordered table-striped mb-0">
                    <thead>
                        <tr>
                            <th>الكود</th>
                            <th>الاسم</th>
                            <th>القطاع</th>
                            <th>سنة / مرحلة</th>
                            <th>المطلوب</th>
                            <th>المدفوع</th>
                            <th>المتبقي</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($booked as $row)
                            <tr>
                                <td>{{ $row->ShamandoraCode }}</td>
                                <td>{{ $row->PersonFullName }}</td>
                                <td>{{ $row->QetaaName ?? '-' }}</td>
                                <td>{{ $row->SanaMarhalaName ?? '-' }}</td>
                                <td>{{ number_format((float) $row->FinalRequiredAmount, 2) }}</td>
                                <td>{{ number_format((float) $row->AmountPaid, 2) }}</td>
                                <td>{{ number_format((float) $row->RemainingAmount, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">لا يوجد محجوزون</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header">قائمة الانتظار</div>
            <div class="card-body p-0">
                <table class="table table-bordered table-striped mb-0">
                    <thead>
                        <tr>
                            <th>الكود</th>
                            <th>الاسم</th>
                            <th>القطاع</th>
                            <th>سنة / مرحلة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($waitingList as $row)
                            <tr>
                                <td>{{ $row->ShamandoraCode }}</td>
                                <td>{{ $row->PersonFullName }}</td>
                                <td>{{ $row->QetaaName ?? '-' }}</td>
                                <td>{{ $row->SanaMarhalaName ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center">لا يوجد أشخاص في قائمة الانتظار</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
