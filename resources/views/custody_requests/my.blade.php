@extends('layouts.app', ['pageTitle' => 'حالة الطلبات'])

@section('content')
    <div class="container mx-auto px-4 py-8" dir="rtl">

        <div class="mb-8 text-center">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">حالة الطلبات</h1>
            <p class="text-gray-600">تابع حالة طلبات العهدة الخاصة بك</p>
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
                <a href="{{ route('custody_requests.create') }}"
                    class="inline-flex items-center justify-center h-10 px-6 text-sm font-medium rounded-full
                       bg-green-50 text-green-700 hover:bg-green-100 transition border border-green-200">
                    طلب عهدة جديد
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-center border border-slate-200 rounded-lg overflow-hidden">
                    <thead class="bg-slate-50">
                        <tr class="text-sm text-slate-700">
                            <th class="p-3 border-b">رقم الطلب</th>
                            <th class="p-3 border-b">من</th>
                            <th class="p-3 border-b">إلى</th>
                            <th class="p-3 border-b">البيانات</th>
                            <th class="p-3 border-b">الحالة</th>
                            <th class="p-3 border-b">المُراجع</th>
                            <th class="p-3 border-b">التفاصيل</th>
                            <th class="p-3 border-b">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm text-slate-800">
                        @forelse ($requests as $idx => $r)
                            <tr
                                class="border-b hover:bg-slate-50 transition {{ $idx % 2 ? 'bg-white' : 'bg-slate-50/40' }}">
                                <td class="p-3 font-semibold text-slate-900">#{{ $r->RequestID }}</td>
                                <td class="p-3">{{ $r->DateFrom }}</td>
                                <td class="p-3">{{ $r->DateTo }}</td>

                                <td class="p-3">
                                    <div class="flex flex-col gap-1 items-center">
                                        <span
                                            class="px-3 py-1 rounded-full text-xs bg-blue-50 text-blue-700 border border-blue-200">
                                            القطاع: {{ $r->QetaaName ?? '—' }}
                                        </span>
                                        <span
                                            class="px-3 py-1 rounded-full text-xs bg-slate-50 text-slate-700 border border-slate-200">
                                            الفعالية: {{ $r->EventTypeName ?? '—' }}
                                        </span>
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
                                            class="px-3 py-1 rounded-full text-xs bg-red-50 text-red-700 border border-red-200">مرفوض</span>
                                    @endif
                                </td>

                                <td class="p-3 text-gray-600">
                                    {{ $r->ReviewerName ?? '—' }}
                                </td>

                                <td class="p-3">
                                    <a href="{{ route('custody_requests.show', $r->RequestID) }}"
                                        class="px-3 py-2 text-xs rounded-lg bg-blue-50 text-blue-700 hover:bg-blue-100 transition border border-blue-200">
                                        عرض
                                    </a>
                                </td>

                                <td class="p-3">
                                    @if ($r->Status === 'pending')
                                        <div class="flex items-center justify-center gap-2">
                                            <a href="{{ route('custody_requests.edit', $r->RequestID) }}"
                                                class="px-3 py-2 text-xs rounded-lg bg-green-50 text-green-700 hover:bg-green-100 transition border border-green-200">
                                                تعديل
                                            </a>

                                            <form method="POST"
                                                action="{{ route('custody_requests.destroy', $r->RequestID) }}"
                                                onsubmit="return confirm('هل أنت متأكد من حذف الطلب؟');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="px-3 py-2 text-xs rounded-lg bg-red-50 text-red-700 hover:bg-red-100 transition border border-red-200">
                                                    حذف
                                                </button>
                                            </form>
                                        </div>
                                    @else
                                        <span class="text-xs text-gray-400">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="p-8 text-gray-500" colspan="8">لا توجد طلبات بعد.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
@endsection
