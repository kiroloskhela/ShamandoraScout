@extends('layouts.app', ['pageTitle' => 'تفاصيل الطلب'])

@section('content')
    <div class="container mx-auto px-4 py-8" dir="rtl">

        <div class="mb-6 text-center">
            <h1 class="text-2xl font-bold text-gray-800 mb-2">تفاصيل الطلب رقم #{{ $requestRow->RequestID }}</h1>
            <p class="text-gray-600">من {{ $requestRow->DateFrom }} إلى {{ $requestRow->DateTo }}</p>
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
                    <span class="font-bold text-gray-700">{{ __('Status:') }}</span>
                    @if ($requestRow->Status === 'pending')
                        <span
                            class="px-3 py-1 rounded-full text-xs bg-yellow-50 text-yellow-700 border border-yellow-200">قيد
                            المراجعة</span>
                    @elseif ($requestRow->Status === 'approved')
                        <span class="px-3 py-1 rounded-full text-xs bg-green-50 text-green-700 border border-green-200">تمت
                            الموافقة</span>
                    @else
                        <span
                            class="px-3 py-1 rounded-full text-xs bg-red-50 text-red-700 border border-red-200">{{ __('Rejected') }}</span>
                    @endif
                </div>

                <div class="flex items-center gap-2">
                    <a href="{{ route('custody_requests.my') }}"
                        class="px-4 py-2 text-xs rounded-lg bg-gray-50 text-gray-700 hover:bg-gray-100 transition border border-gray-200">{{ __('Back') }}</a>

                    @if ($requestRow->Status === 'pending')
                        <a href="{{ route('custody_requests.edit', $requestRow->RequestID) }}"
                            class="px-4 py-2 text-xs rounded-lg bg-green-50 text-green-700 hover:bg-green-100 transition border border-green-200">{{ __('Edit') }}</a>

                        <form method="POST" action="{{ route('custody_requests.destroy', $requestRow->RequestID) }}"
                            onsubmit="return confirm('هل أنت متأكد من حذف الطلب؟');">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                class="px-4 py-2 text-xs rounded-lg bg-red-50 text-red-700 hover:bg-red-100 transition border border-red-200">{{ __('Delete') }}</button>
                        </form>
                    @endif
                </div>
            </div>

            {{-- Badges --}}
            <div class="mt-4 flex flex-wrap gap-2">
                <span class="px-3 py-1 rounded-full text-xs bg-blue-50 text-blue-700 border border-blue-200">
                    القطاع: {{ $requestRow->QetaaName ?? '—' }}
                </span>
                <span class="px-3 py-1 rounded-full text-xs bg-slate-50 text-slate-700 border border-slate-200">
                    نوع الفعالية: {{ $requestRow->EventTypeName ?? '—' }}
                </span>
                <span class="px-3 py-1 rounded-full text-xs bg-purple-50 text-purple-700 border border-purple-200">
                    المراجع: {{ $requestRow->ReviewerName ?? '—' }}
                </span>
            </div>

            {{-- Notes --}}
            @if (!empty($requestRow->UserNote))
                <div class="mt-5 p-3 rounded-lg bg-slate-50 border border-slate-200 text-sm text-slate-700">
                    <div class="font-bold mb-1">ملاحظتك:</div>
                    <div class="leading-6">{{ $requestRow->UserNote }}</div>
                </div>
            @endif

            @if (!empty($requestRow->AdminNote))
                <div
                    class="mt-4 p-3 rounded-lg bg-blue-50 border border-blue-200 text-sm text-blue-800 whitespace-pre-line">
                    <div class="font-bold mb-1">ملاحظة :</div>
                    {{ $requestRow->AdminNote }}
                </div>
            @endif
        </div>

        {{-- Items --}}
        <div class="bg-white rounded-lg shadow-lg p-6 border-2 border-yellow-300">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-bold text-gray-800">الأصناف</h2>
                <span class="text-xs text-gray-500">{{ count($items) }} صنف</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-center border border-slate-200 rounded-lg overflow-hidden">
                    <thead class="bg-slate-50">
                        <tr class="text-sm text-slate-700">
                            <th class="p-3 border-b">م</th>
                            <th class="p-3 border-b">{{ __('Item') }}</th>
                            <th class="p-3 border-b">{{ __('Unit') }}</th>
                            <th class="p-3 border-b">المطلوب</th>
                            <th class="p-3 border-b">المعتمد</th>
                            <th class="p-3 border-b">{{ __('Note') }}</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm text-slate-800">
                        @forelse ($items as $idx => $it)
                            @php
                                $requested = (int) $it->QtyRequested;
                                $approved = is_null($it->QtyApproved) ? null : (int) $it->QtyApproved;
                                $reduced = !is_null($approved) && $approved < $requested;
                                $rowClass = $reduced ? 'bg-yellow-50/60' : ($idx % 2 ? 'bg-white' : 'bg-slate-50/40');
                            @endphp
                            <tr class="border-b hover:bg-slate-50 transition {{ $rowClass }}">
                                <td class="p-3">{{ $idx + 1 }}</td>
                                <td class="p-3 text-right font-medium text-slate-900">
                                    {{ $it->ItemNameSnapshot }}
                                    @if ($reduced)
                                        <div class="text-xs text-yellow-700 mt-1">تم تقليل الكمية</div>
                                    @endif
                                </td>
                                <td class="p-3">{{ $it->ItemUnitSnapshot }}</td>
                                <td class="p-3 font-semibold">{{ $requested }}</td>
                                <td class="p-3 font-semibold {{ $reduced ? 'text-yellow-800' : 'text-slate-900' }}">
                                    @if ($requestRow->Status === 'pending')
                                        <span class="text-gray-500">—</span>
                                    @else
                                        {{ $approved }}
                                    @endif
                                </td>
                                <td class="p-3 text-right text-xs text-gray-600">{{ $it->AdminItemNote ?? '' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td class="p-8 text-gray-500" colspan="6">لا توجد أصناف داخل هذا الطلب.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <p class="mt-4 text-xs text-gray-500">الصفوف باللون الأصفر تعني أن الكمية المعتمدة أقل من المطلوبة.</p>
        </div>

    </div>
@endsection
