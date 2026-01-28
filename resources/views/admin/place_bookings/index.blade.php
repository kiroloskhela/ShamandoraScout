@extends('layouts.app', ['pageTitle' => 'طلبات حجز الأماكن'])

@section('content')
    <div class="container mx-auto px-4 py-8" dir="rtl">

        {{-- Header --}}
        <div class="mb-8 text-center">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">طلبات حجز الأماكن</h1>
            <p class="text-gray-600">مراجعة واعتماد/رفض طلبات المستخدمين</p>
        </div>

        {{-- Alerts --}}
        @if (session('success'))
            <div class="mb-6 p-3 rounded-lg bg-green-50 border border-green-200 text-green-700 text-sm">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="mb-6 p-3 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm">
                {{ session('error') }}
            </div>
        @endif

        {{-- Summary Cards --}}
        @php
            $pendingCount = $bookings->where('Status', 'pending')->count();
            $approvedCount = $bookings->where('Status', 'approved')->count();
            $rejectedCount = $bookings->where('Status', 'rejected')->count();
            $allCount = $bookings->count();
        @endphp

        <div class="grid md:grid-cols-4 gap-4 mb-6 text-center">
            <div class="bg-white rounded-lg shadow-lg p-4 border-2 border-blue-200">
                <div class="text-sm text-gray-500 mb-1">الإجمالي</div>
                <div class="text-2xl font-bold text-blue-800">{{ $allCount }}</div>
            </div>
            <div class="bg-white rounded-lg shadow-lg p-4 border-2 border-yellow-200">
                <div class="text-sm text-gray-500 mb-1">قيد المراجعة</div>
                <div class="text-2xl font-bold text-yellow-700">{{ $pendingCount }}</div>
            </div>
            <div class="bg-white rounded-lg shadow-lg p-4 border-2 border-green-200">
                <div class="text-sm text-gray-500 mb-1">تمت الموافقة</div>
                <div class="text-2xl font-bold text-green-700">{{ $approvedCount }}</div>
            </div>
            <div class="bg-white rounded-lg shadow-lg p-4 border-2 border-red-200">
                <div class="text-sm text-gray-500 mb-1">مرفوض</div>
                <div class="text-2xl font-bold text-red-700">{{ $rejectedCount }}</div>
            </div>
        </div>

        {{-- Filters --}}
        @php
            $filter = request('status', 'all');
            $filtered = $bookings;
            if ($filter !== 'all') {
                $filtered = $bookings->where('Status', $filter);
            }
        @endphp

        <div class="bg-white rounded-lg shadow-lg p-6 mb-6 border-2 border-blue-200">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div class="font-bold text-gray-800">فلتر</div>

                <div class="flex items-center gap-2 flex-wrap">
                    <a href="{{ route('admin.place_bookings.index', ['status' => 'all']) }}"
                        class="px-4 py-2 text-xs rounded-full border transition
                               {{ $filter === 'all' ? 'bg-blue-50 text-blue-700 border-blue-200' : 'bg-white text-gray-700 border-slate-200 hover:bg-slate-50' }}">
                        الكل
                    </a>
                    <a href="{{ route('admin.place_bookings.index', ['status' => 'pending']) }}"
                        class="px-4 py-2 text-xs rounded-full border transition
                               {{ $filter === 'pending' ? 'bg-yellow-50 text-yellow-700 border-yellow-200' : 'bg-white text-gray-700 border-slate-200 hover:bg-slate-50' }}">
                        قيد المراجعة
                    </a>
                    <a href="{{ route('admin.place_bookings.index', ['status' => 'approved']) }}"
                        class="px-4 py-2 text-xs rounded-full border transition
                               {{ $filter === 'approved' ? 'bg-green-50 text-green-700 border-green-200' : 'bg-white text-gray-700 border-slate-200 hover:bg-slate-50' }}">
                        تمت الموافقة
                    </a>
                    <a href="{{ route('admin.place_bookings.index', ['status' => 'rejected']) }}"
                        class="px-4 py-2 text-xs rounded-full border transition
                               {{ $filter === 'rejected' ? 'bg-red-50 text-red-700 border-red-200' : 'bg-white text-gray-700 border-slate-200 hover:bg-slate-50' }}">
                        مرفوض
                    </a>
                </div>
            </div>

            <p class="mt-3 text-xs text-gray-500">
                ملاحظة: يمكنك فتح أي طلب للموافقة/الرفض أو الموافقة مع تعديل المكان/الوقت مع إضافة ملاحظات.
            </p>
        </div>

        {{-- Table --}}
        <div class="bg-white rounded-lg shadow-lg p-6 border-2 border-slate-200">
            <div class="overflow-x-auto">
                <table class="w-full text-center border border-slate-200 rounded-lg overflow-hidden">
                    <thead class="bg-slate-50">
                        <tr class="text-sm text-slate-700">
                            <th class="p-3 border-b">رقم الطلب</th>
                            <th class="p-3 border-b">المستخدم</th>
                            <th class="p-3 border-b">الموقع</th>
                            <th class="p-3 border-b">المكان</th>
                            <th class="p-3 border-b">التاريخ</th>
                            <th class="p-3 border-b">من</th>
                            <th class="p-3 border-b">إلى</th>
                            <th class="p-3 border-b">الحالة</th>
                            <th class="p-3 border-b">تاريخ الإرسال</th>
                            <th class="p-3 border-b">التفاصيل</th>
                        </tr>
                    </thead>

                    <tbody class="text-sm text-slate-800">
                        @forelse ($filtered as $idx => $r)
                            @php
                                $rowClass = $idx % 2 ? 'bg-white' : 'bg-slate-50/40';
                            @endphp
                            <tr class="border-b hover:bg-slate-50 transition {{ $rowClass }}">
                                <td class="p-3 font-semibold text-slate-900">#{{ $r->BookingID }}</td>
                                <td class="p-3">{{ $r->UserName ?? '—' }}</td>
                                <td class="p-3">{{ $r->LocationName ?? '—' }}</td>
                                <td class="p-3">{{ $r->PlaceName ?? '—' }}</td>
                                <td class="p-3">{{ $r->BookingDate ?? '—' }}</td>
                                <td class="p-3">{{ $r->TimeFrom ?? '—' }}</td>
                                <td class="p-3">{{ $r->TimeTo ?? '—' }}</td>

                                <td class="p-3">
                                    @if ($r->Status === 'pending')
                                        <span
                                            class="px-3 py-1 rounded-full text-xs bg-yellow-50 text-yellow-700 border border-yellow-200">
                                            قيد المراجعة
                                        </span>
                                    @elseif ($r->Status === 'approved')
                                        <span
                                            class="px-3 py-1 rounded-full text-xs bg-green-50 text-green-700 border border-green-200">
                                            تمت الموافقة
                                        </span>
                                    @else
                                        <span
                                            class="px-3 py-1 rounded-full text-xs bg-red-50 text-red-700 border border-red-200">
                                            مرفوض
                                        </span>
                                    @endif
                                </td>

                                <td class="p-3 text-gray-600">
                                    {{ !empty($r->created_at) ? \Carbon\Carbon::parse($r->created_at)->format('Y-m-d') : '—' }}
                                </td>

                                <td class="p-3">
                                    <a href="{{ route('admin.place_bookings.show', $r->BookingID) }}"
                                        class="inline-flex items-center justify-center px-4 py-2 text-xs rounded-lg
                                               bg-blue-50 text-blue-700 hover:bg-blue-100 transition border border-blue-200">
                                        فتح
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="p-8 text-gray-500" colspan="10">لا توجد طلبات مطابقة للفلتر.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
@endsection
