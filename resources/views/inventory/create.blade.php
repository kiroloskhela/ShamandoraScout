@extends('layouts.app', ['pageTitle' => 'إضافة عنصر جديد للمخزون'])

@section('content')
    <div class="flex place-content-center">
        <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-md border-2 border-blue-300" dir="rtl">
            <!-- Title -->
            <div class="mb-6 text-center">
                <h2 class="text-xl font-bold text-gray-800">إضافة عنصر جديد</h2>
            </div>

            <form method="POST" action="{{ route('inventory.insert') }}">
                @csrf
                <div class="space-y-6">
                    <!-- Item Name -->
                    <div class="relative">
                        <label for="item_name" class="block mb-2 text-sm text-gray-700">اسم العنصر</label>
                        <input type="text" id="item_name" name="item_name" required
                            class="w-full h-12 px-4 border rounded-lg text-right border-slate-200 text-slate-600 
                               focus:border-blue-500 focus:outline-none"
                            placeholder="ادخل اسم العنصر">
                    </div>

                    <!-- Quantity -->
                    <div class="relative">
                        <label for="item_quantity" class="block mb-2 text-sm text-gray-700">الكمية</label>
                        <input type="number" id="item_quantity" name="item_quantity"
                            class="w-full h-12 px-4 border rounded-lg text-right border-slate-200 text-slate-600 
                               focus:border-blue-500 focus:outline-none"
                            placeholder="ادخل الكمية">
                    </div>

                    <!-- Measuring Unit -->
                    <div class="relative">
                        <label for="item_measuring_unit" class="block mb-2 text-sm text-gray-700">وحدة القياس</label>
                        <select id="item_measuring_unit" name="item_measuring_unit"
                            class="w-full h-12 px-4 border rounded-lg text-right border-slate-200 text-slate-600 
                               focus:border-blue-500 focus:outline-none">
                            <option value="">-- اختر الوحدة --</option>
                            @foreach ($units as $unit)
                                <option value="{{ $unit }}">{{ $unit }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Submit -->
                    <div class="flex justify-center">
                        <button type="submit"
                            class="inline-flex items-center justify-center h-12 px-8 text-sm font-medium tracking-wide 
                               rounded-full bg-blue-50 text-blue-500 hover:bg-blue-100 hover:text-blue-600 transition">
                            إضافة العنصر
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
