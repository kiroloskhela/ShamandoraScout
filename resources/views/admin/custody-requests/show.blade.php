@extends('layouts.app', ['pageTitle' => __('Review custody request')])

@section('content')
    <div class="container mx-auto px-4 py-8">

        <div class="mb-6 text-center">
            <h1 class="text-2xl font-bold text-gray-800 mb-2">مراجعة طلب عهدة رقم #{{ $requestRow->RequestID }}</h1>
            <p class="text-gray-600">من {{ $requestRow->DateFrom }} إلى {{ $requestRow->DateTo }}</p>
        </div>

        {{-- Alerts --}}
        @if ($errors->any())
            <div class="mb-6 p-3 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm">
                <ul class="list-disc pr-5 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li class="leading-5">{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
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

        {{-- Top Card --}}
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6 border-2 border-slate-200">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div class="text-sm text-gray-700 flex items-center gap-2">
                    <span class="font-bold">{{ __('Status:') }}</span>
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

                <a href="{{ route('admin.custody_requests.index') }}"
                    class="px-4 py-2 text-xs rounded-lg bg-gray-50 text-gray-700 hover:bg-gray-100 transition border border-gray-200">
                    رجوع للقائمة
                </a>
            </div>

            @if (!empty($requestRow->UserNote))
                <div class="mt-5 p-3 rounded-lg bg-slate-50 border border-slate-200 text-sm text-slate-700">
                    <div class="font-bold mb-1">ملاحظة مقدم الطلب:</div>
                    <div class="leading-6">{{ $requestRow->UserNote }}</div>
                </div>
            @endif

            @if (!empty($requestRow->AdminNote))
                <div
                    class="mt-4 p-3 rounded-lg bg-blue-50 border border-blue-200 text-sm text-blue-800 whitespace-pre-line">
                    <div class="font-bold mb-1">ملاحظة:</div>
                    {{ $requestRow->AdminNote }}
                </div>
            @endif
        </div>

        {{-- Items + Approve --}}
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6 border-2 border-yellow-300">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-bold text-gray-800">الأصناف</h2>
                <span class="text-xs text-gray-500">{{ count($items) }} صنف</span>
            </div>

            @if ($requestRow->Status === 'pending')
                <form method="POST" action="{{ route('admin.custody_requests.approve', $requestRow->RequestID) }}">
                    @csrf

                    <div class="overflow-x-auto">
                        <table class="w-full text-center border border-slate-200 rounded-lg overflow-hidden">
                            <thead class="bg-slate-50">
                                <tr class="text-sm text-slate-700">
                                    <th class="p-3 border-b">م</th>
                                    <th class="p-3 border-b">{{ __('Item') }}</th>
                                    <th class="p-3 border-b">{{ __('Unit') }}</th>
                                    <th class="p-3 border-b">المطلوب</th>
                                    <th class="p-3 border-b">المعتمد</th>
                                    <th class="p-3 border-b">ملاحظة على الصنف</th>
                                </tr>
                            </thead>
                            <tbody class="text-sm text-slate-800">
                                @foreach ($items as $idx => $it)
                                    <tr
                                        class="border-b hover:bg-slate-50 transition {{ $idx % 2 ? 'bg-white' : 'bg-slate-50/40' }}">
                                        <td class="p-3">{{ $idx + 1 }}</td>
                                        <td class="p-3 text-right font-medium text-slate-900">{{ $it->ItemNameSnapshot }}
                                        </td>
                                        <td class="p-3">{{ $it->ItemUnitSnapshot }}</td>
                                        <td class="p-3 font-semibold">{{ $it->QtyRequested }}</td>
                                        <td class="p-3">
                                            <input type="number" name="approved_qty[{{ $it->RequestItemID }}]"
                                                min="0" max="{{ $it->QtyRequested }}"
                                                value="{{ old('approved_qty.' . $it->RequestItemID, $it->QtyRequested) }}"
                                                class="w-24 h-10 border rounded-lg text-center border-slate-200 focus:border-blue-500 focus:outline-none">
                                            <div class="text-[11px] text-gray-500 mt-1">حد أقصى: {{ $it->QtyRequested }}
                                            </div>
                                        </td>
                                        <td class="p-3">
                                            <input type="text" name="item_note[{{ $it->RequestItemID }}]"
                                                value="{{ old('item_note.' . $it->RequestItemID) }}"
                                                class="w-full h-10 border rounded-lg px-3 text-right border-slate-200 focus:border-blue-500 focus:outline-none"
                                                placeholder="{{ __('Optional') }}">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-5">
                        <label class="block mb-2 text-sm text-gray-700">ملاحظة عامة للمستخدم (اختياري)</label>
                        <textarea name="admin_note" rows="3"
                            class="w-full border rounded-lg p-3 text-right border-slate-200 text-slate-700 focus:border-blue-500 focus:outline-none"
                            placeholder="مثال: يرجى الاستلام من المخزن...">{{ old('admin_note') }}</textarea>
                        <p class="mt-2 text-xs text-gray-500">سيتم إضافة ملاحظة تلقائية إذا تم تقليل أي كمية.</p>
                    </div>

                    <div class="mt-6 flex justify-center gap-3">
                        <button type="submit"
                            class="inline-flex items-center justify-center h-12 px-10 text-sm font-medium rounded-full
                                   bg-green-50 text-green-700 hover:bg-green-100 transition border border-green-200">
                            اعتماد
                        </button>
                    </div>
                </form>
            @else
                {{-- Read-only view after review --}}
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
                            @foreach ($items as $idx => $it)
                                @php
                                    $requested = (int) $it->QtyRequested;
                                    $approved = is_null($it->QtyApproved) ? null : (int) $it->QtyApproved;
                                    $reduced = !is_null($approved) && $approved < $requested;
                                    $rowClass = $reduced
                                        ? 'bg-yellow-50/60'
                                        : ($idx % 2
                                            ? 'bg-white'
                                            : 'bg-slate-50/40');
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
                                        {{ $approved ?? '—' }}
                                    </td>
                                    <td class="p-3 text-right text-xs text-gray-600">{{ $it->AdminItemNote ?? '' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <p class="mt-4 text-xs text-gray-500">
                    ملاحظة: الصفوف باللون الأصفر تعني أن الكمية المعتمدة أقل من المطلوبة.
                </p>
            @endif
        </div>

        {{-- Reject (only pending) --}}
        @if ($requestRow->Status === 'pending')
            <div class="bg-white rounded-lg shadow-lg p-6 border-2 border-red-200">
                <h2 class="text-lg font-bold text-gray-800 mb-4">رفض الطلب</h2>

                <form method="POST" action="{{ route('admin.custody_requests.reject', $requestRow->RequestID) }}">
                    @csrf

                    <label class="block mb-2 text-sm text-gray-700">سبب الرفض (اختياري)</label>
                    <textarea name="admin_note" rows="2"
                        class="w-full border rounded-lg p-3 text-right border-slate-200 text-slate-700 focus:border-red-500 focus:outline-none"
                        placeholder="مثال: عدم توفر الأصناف...">{{ old('admin_note') }}</textarea>

                    <div class="mt-4 flex justify-center">
                        <button type="submit"
                            class="inline-flex items-center justify-center h-12 px-10 text-sm font-medium rounded-full
                                   bg-red-50 text-red-700 hover:bg-red-100 transition border border-red-200">{{ __('Reject') }}</button>
                    </div>
                </form>
            </div>
        @endif

    </div>
@endsection
