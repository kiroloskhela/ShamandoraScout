@extends('layouts.app', ['pageTitle' => __('Edit inventory item')])

@section('content')
    <div class="flex place-content-center">
        <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-md border-2 border-emerald-300">
            <!-- Title -->
            <div class="mb-6 text-center">
                <h2 class="text-xl font-bold text-gray-800">تعديل بيانات المخزون</h2>
            </div>

            <form method="POST" action="{{ route('inventory.update', $inventory->InventoryID) }}">
                @csrf
                @method('PATCH')

                <div class="space-y-6">
                    <!-- Item Name -->
                    <div class="relative">
                        <label for="item_name" class="block mb-2 text-sm text-gray-700">اسم العنصر</label>
                        <input type="text" id="item_name" name="item_name" value="{{ $inventory->ItemName }}"
                            class="w-full h-12 ps-4 border rounded-lg border-slate-200 text-slate-600 
                               focus:border-emerald-500 focus:outline-none"
                            required>
                    </div>

                    <!-- Item Quantity -->
                    <div class="relative">
                        <label for="item_quantity" class="block mb-2 text-sm text-gray-700">{{ __('Quantity') }}</label>
                        <input type="number" id="item_quantity" name="item_quantity" value="{{ $inventory->ItemQuantity }}"
                            class="w-full h-12 ps-4 border rounded-lg border-slate-200 text-slate-600 
                               focus:border-emerald-500 focus:outline-none">
                    </div>

                    <!-- Item Measuring Unit (Enum from DB) -->
                    <div class="relative">
                        <label for="item_measuring_unit" class="block mb-2 text-sm text-gray-700">وحدة القياس</label>
                        <select id="item_measuring_unit" name="item_measuring_unit"
                            class="w-full h-12 ps-4 border rounded-lg border-slate-200 text-slate-600 
                               focus:border-emerald-500 focus:outline-none">
                            <option value="">-- اختر الوحدة --</option>
                            @foreach ($units as $unit)
                                <option value="{{ $unit }}"
                                    {{ $inventory->ItemMeasuringUnit === $unit ? 'selected' : '' }}>
                                    {{ $unit }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <!-- Category -->
                    <div class="relative">
                        <label for="category" class="block mb-2 text-sm text-gray-700">الفئة</label>
                        <select id="category" name="category"
                            class="w-full h-12 ps-4 border rounded-lg border-slate-200 text-slate-600 
                               focus:border-emerald-500 focus:outline-none">
                            <option value="">-- اختر الفئة --</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category }}"
                                    {{ $inventory->Category === $category ? 'selected' : '' }}>
                                    {{ $category }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <!-- Location -->
                    <div class="relative">
                        <label for="location" class="block mb-2 text-sm text-gray-700">{{ __('Location') }}</label>
                        <select id="location" name="location"
                            class="w-full h-12 ps-4 border rounded-lg border-slate-200 text-slate-600 
                               focus:border-emerald-500 focus:outline-none">
                            <option value="">-- اختر الموقع --</option>
                            @foreach ($locations as $location)
                                <option value="{{ $location }}"
                                    {{ $inventory->Location === $location ? 'selected' : '' }}>
                                    {{ $location }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <!-- Submit -->
                    <div class="flex justify-center">
                        <button type="submit"
                            class="inline-flex items-center justify-center h-12 px-8 text-sm font-medium tracking-wide 
                               rounded-full bg-emerald-50 text-emerald-500 hover:bg-emerald-100 hover:text-emerald-600 transition">
                            تحديث البيانات
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
