@extends('layouts.app', ['pageTitle' => 'حالة طلبات الحجز'])

@section('content')
    <div class="container mx-auto px-4 py-8" dir="rtl">

        <div class="mb-8 text-center">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">حالة طلبات الحجز</h1>
            <p class="text-gray-600">تابع حالة طلبات الحجز الخاصة بك</p>
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

        <div class="bg-white rounded-lg shadow-lg p-6 border-2 border-slate-200">
            <div class="flex items-center justify-between mb-4 flex-wrap gap-3">
                <h2 class="text-lg font-bold text-gray-800">قائمة الطلبات</h2>
                <a href="{{ route('place_bookings.create') }}"
                    class="inline-flex items-center justify-center h-10 px-6 text-sm font-medium rounded-full
                       bg-green-50 text-green-700 hover:bg-green-100 transition border border-green-200">
                    طلب حجز جديد
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-center border border-slate-200 rounded-lg overflow-hidden">
                    <thead class="bg-slate-50">
                        <tr class="text-sm text-slate-700">
                            <th class="p-3 border-b">{{ __('Request number') }}</th>
                            <th class="p-3 border-b">{{ __('Location') }}</th>
                            <th class="p-3 border-b">{{ __('Place') }}</th>
                            <th class="p-3 border-b">{{ __('Date') }}</th>
                            <th class="p-3 border-b">{{ __('From') }}</th>
                            <th class="p-3 border-b">{{ __('To') }}</th>
                            <th class="p-3 border-b">{{ __('Data') }}</th>
                            <th class="p-3 border-b">{{ __('Status') }}</th>
                            <th class="p-3 border-b">المُراجع</th>
                            <th class="p-3 border-b">{{ __('Details') }}</th>
                            <th class="p-3 border-b">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm text-slate-800">
                        @forelse ($rows as $idx => $r)
                            <tr
                                class="border-b hover:bg-slate-50 transition {{ $idx % 2 ? 'bg-white' : 'bg-slate-50/40' }}">
                                <td class="p-3 font-semibold text-slate-900">#{{ $r->BookingID }}</td>
                                <td class="p-3">{{ $r->LocationName ?? '—' }}</td>
                                <td class="p-3">{{ $r->PlaceName ?? '—' }}</td>

                                <td class="p-3">{{ $r->BookingDate ?? '—' }}</td>
                                <td class="p-3">{{ $r->TimeFrom ?? '—' }}</td>
                                <td class="p-3">{{ $r->TimeTo ?? '—' }}</td>

                                <td class="p-3">
                                    <div class="flex flex-col gap-1 items-center">
                                        <span
                                            class="px-3 py-1 rounded-full text-xs bg-blue-50 text-blue-700 border border-blue-200">
                                            القطاع: {{ $r->QetaaName ?? '—' }}
                                        </span>

                                        @if (!empty($r->ApprovedPlaceID) || !empty($r->ApprovedTimeFrom) || !empty($r->ApprovedTimeTo))
                                            <span
                                                class="px-3 py-1 rounded-full text-xs bg-slate-50 text-slate-700 border border-slate-200">
                                                تم التعديل بواسطة الإدارة
                                            </span>
                                        @else
                                            <span
                                                class="px-3 py-1 rounded-full text-xs bg-slate-50 text-slate-700 border border-slate-200">
                                                بدون تعديل
                                            </span>
                                        @endif
                                    </div>
                                </td>

                                <td class="p-3">
                                    @if ($r->Status === 'pending')
                                        <span
                                            class="px-3 py-1 rounded-full text-xs bg-yellow-50 text-yellow-700 border border-yellow-200">قيد
                                            المراجعة</span>
                                    @elseif ($r->Status === 'approved')
                                        <span
                                            class="px-3 py-1 rounded-full text-xs bg-green-50 text-green-700 border border-green-200">تمت
                                            الموافقة</span>
                                    @else
                                        <span
                                            class="px-3 py-1 rounded-full text-xs bg-red-50 text-red-700 border border-red-200">{{ __('Rejected') }}</span>
                                    @endif
                                </td>

                                <td class="p-3 text-gray-600">
                                    {{ $r->ReviewerName ?? '—' }}
                                </td>

                                <td class="p-3">
                                    <a href="{{ route('place_bookings.show', $r->BookingID) }}"
                                        class="px-3 py-2 text-xs rounded-lg bg-blue-50 text-blue-700 hover:bg-blue-100 transition border border-blue-200">{{ __('View') }}</a>
                                </td>

                                <td class="p-3">
                                    @if ($r->Status === 'pending')
                                        <div class="flex items-center justify-center gap-2">
                                            <a href="{{ route('place_bookings.edit', $r->BookingID) }}"
                                                class="px-3 py-2 text-xs rounded-lg bg-green-50 text-green-700 hover:bg-green-100 transition border border-green-200">{{ __('Edit') }}</a>

                                            <form method="POST"
                                                action="{{ route('place_bookings.destroy', $r->BookingID) }}"
                                                onsubmit="return confirm('هل أنت متأكد من حذف الطلب؟');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="px-3 py-2 text-xs rounded-lg bg-red-50 text-red-700 hover:bg-red-100 transition border border-red-200">{{ __('Delete') }}</button>
                                            </form>
                                        </div>
                                    @else
                                        <span class="text-xs text-gray-400">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="p-8 text-gray-500" colspan="11">لا توجد طلبات بعد.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
@endsection
