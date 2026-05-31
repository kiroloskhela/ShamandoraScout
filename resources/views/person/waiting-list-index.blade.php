{{-- resources/views/person/waiting-list-index.blade.php --}}
@extends('layouts.app')

@section('content')

    <div dir="rtl" class="container-fluid py-4">

        {{-- ── Flash Messages ─────────────────────────────────────────── --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- ── Page Header ─────────────────────────────────────────────── --}}
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h2 class="mb-0 fw-bold">
                    <i class="fas fa-clock text-warning me-2"></i>
                    قائمة الانتظار
                </h2>
                <small class="text-muted">
                    إجمالي المنتظرين: <strong>{{ $persons->count() }}</strong>
                </small>
            </div>
            <a href="{{ route('person.new-enrolments-index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> العودة للتسجيلات
            </a>
        </div>

        {{-- ── Table ───────────────────────────────────────────────────── --}}
        @if ($persons->isEmpty())
            <div class="alert alert-info text-center py-5">
                <i class="fas fa-inbox fa-3x mb-3 d-block opacity-50"></i>
                قائمة الانتظار فارغة حالياً
            </div>
        @else
            <div class="card shadow-sm border-0">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th scope="col" class="text-center">#</th>
                                    <th scope="col">كود شمندورة</th>
                                    <th scope="col">الاسم الكامل</th>
                                    <th scope="col">القطاع</th>
                                    <th scope="col">المرحلة</th>
                                    <th scope="col">الرقم القومي</th>
                                    <th scope="col">رقم الهاتف</th>
                                    <th scope="col" class="text-center">أجاب الأسئلة؟</th>
                                    <th scope="col" class="text-center">الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($persons as $index => $person)
                                    <tr>
                                        <td class="text-center text-muted">{{ $index + 1 }}</td>

                                        <td>
                                            <span class="badge bg-secondary font-monospace">
                                                {{ $person->ShamandoraCode ?? '—' }}
                                            </span>
                                        </td>

                                        <td class="fw-semibold">{{ $person->FullName }}</td>

                                        <td>{{ $person->QetaaName ?? '—' }}</td>

                                        <td>{{ $person->SanaMarhalaName ?? '—' }}</td>

                                        <td class="font-monospace text-muted small">
                                            {{ $person->RaqamQawmy ?? '—' }}
                                        </td>

                                        <td class="small">{{ $person->PersonPersonalMobileNumber ?? '—' }}</td>

                                        <td class="text-center">
                                            @if ($person->HasAnsweredQuestions === 'نعم')
                                                <span class="badge bg-success">نعم</span>
                                            @else
                                                <span class="badge bg-secondary">لا</span>
                                            @endif
                                        </td>

                                        <td class="text-center">
                                            <div class="d-flex gap-2 justify-content-center">

                                                {{-- ── Migrate Button ── --}}
                                                <form method="POST"
                                                    action="{{ route('person.waiting-list-migrate', $person->PersonID) }}"
                                                    onsubmit="return confirm('هل أنت متأكد من نقل هذا الشخص إلى قائمة التسجيل؟')">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-success">
                                                        <i class="fas fa-arrow-circle-up me-1"></i>
                                                        نقل للتسجيل
                                                    </button>
                                                </form>

                                                {{-- ── Decline Button ── --}}
                                                <form method="POST"
                                                    action="{{ route('person.waiting-list-decline', $person->PersonID) }}"
                                                    onsubmit="return confirm('هل أنت متأكد من رفض وحذف هذا الطلب نهائياً؟')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger">
                                                        <i class="fas fa-times-circle me-1"></i>
                                                        رفض
                                                    </button>
                                                </form>

                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

    </div>

@endsection
