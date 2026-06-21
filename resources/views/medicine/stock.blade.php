@extends('layouts.app', ['pageTitle' => 'توزيع مخزون الدواء'])

@section('content')
    <div class="container mx-auto px-4 py-8" dir="rtl">
        <div class="bg-white rounded-lg shadow-lg p-6 max-w-4xl mx-auto border-2 border-cyan-300">
            <div class="mb-6 text-center">
                <h1 class="text-2xl font-bold text-gray-800">توزيع مخزون الدواء</h1>
                <p class="mt-2 text-sm text-gray-600">{{ $medicine->MedicineName }} - {{ $medicine->TypeLabel }}</p>
            </div>

            @if (session('status'))
                <div class="mb-4 p-3 rounded-lg bg-green-100 text-green-800 text-sm text-center">
                    {{ session('status') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 p-3 rounded-lg bg-red-100 text-red-800 text-sm text-center">
                    {{ session('error') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-4 p-3 rounded-lg bg-red-100 text-red-800 text-sm">
                    <ul class="list-disc pr-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid md:grid-cols-3 gap-4 mb-6">
                <div class="rounded-lg bg-slate-50 border border-slate-200 p-4">
                    <div class="text-xs text-slate-500">إجمالي المخزون</div>
                    <div class="mt-1 text-lg font-bold text-slate-800">{{ $medicine->AmountText }}</div>
                </div>
                <div class="rounded-lg bg-emerald-50 border border-emerald-200 p-4">
                    <div class="text-xs text-emerald-600">المتاح حالياً</div>
                    <div class="mt-1 text-lg font-bold text-emerald-800">{{ $medicine->AvailableText }}</div>
                </div>
                <div class="rounded-lg bg-amber-50 border border-amber-200 p-4">
                    <div class="text-xs text-amber-600">المحجوز حالياً</div>
                    <div class="mt-1 text-lg font-bold text-amber-800">{{ $medicine->LockedText }}</div>
                </div>
            </div>

            <form method="POST" action="{{ route('medicine.stock.update', $medicine->MedicineID) }}">
                @csrf
                @method('PATCH')

                <div class="mb-4 p-3 rounded-lg bg-cyan-50 text-cyan-800 text-sm text-center">
                    مجموع الكميات في الأماكن يجب أن يساوي إجمالي المخزون: {{ $medicine->AmountText }}.
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-right border border-slate-200">
                        <thead class="bg-slate-50 text-sm text-slate-700">
                            <tr>
                                <th class="p-3 border-b">المكان</th>
                                <th class="p-3 border-b">الكمية</th>
                                <th class="p-3 border-b">المحجوز</th>
                                <th class="p-3 border-b">المتاح بعد الحجز</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm text-slate-800">
                            @foreach ($locations as $location)
                                <tr>
                                    <td class="p-3 border-b">
                                        <div class="font-bold">{{ $location->LocationName }}</div>
                                        @if (!$location->IsActive)
                                            <div class="mt-1 text-xs text-red-500">غير مفعل</div>
                                        @endif
                                    </td>
                                    <td class="p-3 border-b">
                                        <div class="flex rounded-lg border border-slate-200 overflow-hidden w-48">
                                            <input type="number" min="{{ $location->LockedAmount }}" step="1"
                                                name="amounts[{{ $location->LocationID }}]"
                                                value="{{ old('amounts.' . $location->LocationID, $location->Amount) }}"
                                                class="w-full h-11 px-3 text-right focus:outline-none">
                                            <span
                                                class="inline-flex items-center px-3 bg-slate-50 text-xs text-slate-600 border-r border-slate-200">{{ $medicine->UnitLabel }}</span>
                                        </div>
                                    </td>
                                    <td class="p-3 border-b text-amber-700 font-bold">
                                        {{ $location->LockedAmount }} {{ $medicine->UnitLabel }}
                                    </td>
                                    <td class="p-3 border-b text-emerald-700 font-bold">
                                        {{ $location->AvailableAmount }} {{ $medicine->UnitLabel }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="flex justify-center gap-3 mt-8">
                    <button type="submit"
                        class="inline-flex items-center justify-center h-12 px-8 text-sm font-medium rounded-full bg-cyan-50 text-cyan-700 hover:bg-cyan-100 transition">
                        حفظ التوزيع
                    </button>
                    <a href="{{ route('medicine.index') }}"
                        class="inline-flex items-center justify-center h-12 px-8 text-sm font-medium rounded-full bg-gray-100 text-gray-700 hover:bg-gray-200 transition">
                        رجوع
                    </a>
                </div>
            </form>

            <form method="POST" action="{{ route('medicine.restock', $medicine->MedicineID) }}"
                class="mt-4 text-center"
                onsubmit="return confirm('Restock will move all quantity to ستوك and empty all other places. Continue?');">
                @csrf
                <button type="submit"
                    class="inline-flex items-center justify-center h-12 px-8 text-sm font-medium rounded-full bg-amber-50 text-amber-700 hover:bg-amber-100 transition">
                    Restock
                </button>
            </form>
        </div>
    </div>
@endsection
