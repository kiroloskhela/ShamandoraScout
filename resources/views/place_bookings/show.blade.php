@extends('layouts.app', ['pageTitle' => 'تفاصيل طلب الحجز'])

@section('content')
    <div class="container mx-auto px-4 py-8" dir="rtl">

        <div class="mb-6 text-center">
            <h1 class="text-2xl font-bold text-gray-800 mb-2">تفاصيل طلب الحجز رقم #{{ $booking->BookingID }}</h1>
            <p class="text-gray-600">
                {{ $booking->BookingDate }} • من {{ $booking->TimeFrom }} إلى {{ $booking->TimeTo }}
            </p>
        </div>

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

        <div class="bg-white rounded-lg shadow-lg p-6 mb-6 border-2 border-slate-200">
            <div class="flex items-center justify-between flex-wrap gap-3">
                <div class="flex items-center gap-2">
                    <span class="font-bold text-gray-700">الحالة:</span>
                    @if ($booking->Status === 'pending')
                        <span
                            class="px-3 py-1 rounded-full text-xs bg-yellow-50 text-yellow-700 border border-yellow-200">قيد
                            المراجعة</span>
                    @elseif ($booking->Status === 'approved')
                        <span class="px-3 py-1 rounded-full text-xs bg-green-50 text-green-700 border border-green-200">تمت
                            الموافقة</span>
                    @else
                        <span
                            class="px-3 py-1 rounded-full text-xs bg-red-50 text-red-700 border border-red-200">مرفوض</span>
                    @endif
                </div>

                <div class="flex items-center gap-2">
                    <a href="{{ route('place_bookings.my') }}"
                        class="px-4 py-2 text-xs rounded-lg bg-gray-50 text-gray-700 hover:bg-gray-100 transition border border-gray-200">
                        رجوع
                    </a>

                    @if ($booking->Status === 'pending')
                        <a href="{{ route('place_bookings.edit', $booking->BookingID) }}"
                            class="px-4 py-2 text-xs rounded-lg bg-green-50 text-green-700 hover:bg-green-100 transition border border-green-200">
                            تعديل
                        </a>

                        <form method="POST" action="{{ route('place_bookings.destroy', $booking->BookingID) }}"
                            onsubmit="return confirm('هل أنت متأكد من حذف الطلب؟');">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                class="px-4 py-2 text-xs rounded-lg bg-red-50 text-red-700 hover:bg-red-100 transition border border-red-200">
                                حذف
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            {{-- Badges --}}
            <div class="mt-4 flex flex-wrap gap-2">
                <span class="px-3 py-1 rounded-full text-xs bg-blue-50 text-blue-700 border border-blue-200">
                    الموقع: {{ $booking->LocationName ?? '—' }}
                </span>

                <span class="px-3 py-1 rounded-full text-xs bg-slate-50 text-slate-700 border border-slate-200">
                    المكان: {{ $booking->PlaceName ?? '—' }}
                </span>

                <span class="px-3 py-1 rounded-full text-xs bg-purple-50 text-purple-700 border border-purple-200">
                    القطاع: {{ $booking->QetaaName ?? '—' }}
                </span>

                <span class="px-3 py-1 rounded-full text-xs bg-indigo-50 text-indigo-700 border border-indigo-200">
                    المراجع: {{ $booking->ReviewerName ?? '—' }}
                </span>
            </div>

            {{-- Approved / Edited by admin --}}
            @php
                $hasAdminEdit =
                    !empty($booking->ApprovedPlaceName) ||
                    !empty($booking->ApprovedTimeFrom) ||
                    !empty($booking->ApprovedTimeTo);
            @endphp

            @if ($booking->Status !== 'pending')
                <div class="mt-5 p-4 rounded-lg border border-slate-200 bg-slate-50">
                    <div class="font-bold text-slate-800 mb-2">نتيجة المراجعة</div>

                    @if ($booking->Status === 'approved')
                        <div class="text-sm text-slate-700 leading-6">
                            @if ($hasAdminEdit)
                                <div class="mb-2 text-xs text-slate-500">تم اعتماد الطلب مع تعديل بواسطة الإدارة</div>
                                <div class="grid md:grid-cols-3 gap-3">
                                    <div class="p-3 rounded-lg bg-white border border-slate-200">
                                        <div class="text-xs text-slate-500 mb-1">المكان المعتمد</div>
                                        <div class="font-semibold text-slate-900">{{ $booking->ApprovedPlaceName ?? '—' }}
                                        </div>
                                    </div>

                                    <div class="p-3 rounded-lg bg-white border border-slate-200">
                                        <div class="text-xs text-slate-500 mb-1">من</div>
                                        <div class="font-semibold text-slate-900">{{ $booking->ApprovedTimeFrom ?? '—' }}
                                        </div>
                                    </div>

                                    <div class="p-3 rounded-lg bg-white border border-slate-200">
                                        <div class="text-xs text-slate-500 mb-1">إلى</div>
                                        <div class="font-semibold text-slate-900">{{ $booking->ApprovedTimeTo ?? '—' }}
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="text-xs text-slate-500">تم اعتماد الطلب كما هو بدون تعديل.</div>
                            @endif
                        </div>
                    @endif

                    @if (!empty($booking->AdminNote))
                        <div
                            class="mt-4 p-3 rounded-lg bg-blue-50 border border-blue-200 text-sm text-blue-800 whitespace-pre-line">
                            <div class="font-bold mb-1">ملاحظة الإدارة:</div>
                            {{ $booking->AdminNote }}
                        </div>
                    @endif
                </div>
            @endif

            {{-- Notes --}}
            @if (!empty($booking->UserNote))
                <div class="mt-5 p-3 rounded-lg bg-slate-50 border border-slate-200 text-sm text-slate-700">
                    <div class="font-bold mb-1">ملاحظتك:</div>
                    <div class="leading-6">{{ $booking->UserNote }}</div>
                </div>
            @endif
        </div>

    </div>
@endsection
