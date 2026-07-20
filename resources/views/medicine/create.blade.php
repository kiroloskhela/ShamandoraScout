@extends('layouts.app', ['pageTitle' => __('Add medicine')])

@section('content')
    <div class="flex place-content-center">
        <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-md border-2 border-blue-300">
            <div class="mb-6 text-center">
                <h2 class="text-xl font-bold text-gray-800">إضافة دواء</h2>
            </div>

            @if ($errors->any())
                <div class="mb-4 p-3 rounded-lg bg-red-100 text-red-800 text-sm">
                    <ul class="list-disc pr-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('medicine.insert') }}">
                @csrf

                <div class="space-y-6">
                    <div>
                        <label for="medicine_name" class="block mb-2 text-sm text-gray-700">اسم الدواء</label>
                        <input type="text" id="medicine_name" name="medicine_name" required
                            value="{{ old('medicine_name') }}"
                            class="w-full h-12 ps-4 border rounded-lg border-slate-200 text-slate-600 focus:border-blue-500 focus:outline-none"
                            placeholder="اكتب اسم الدواء">
                    </div>

                    <div>
                        <label for="medicine_type" class="block mb-2 text-sm text-gray-700">نوع الدواء</label>
                        <select id="medicine_type" name="medicine_type" required
                            class="w-full h-12 ps-4 border rounded-lg border-slate-200 text-slate-600 focus:border-blue-500 focus:outline-none">
                            <option value="">-- اختر النوع --</option>
                            @foreach ($types as $value => $type)
                                <option value="{{ $value }}" {{ old('medicine_type') === $value ? 'selected' : '' }}>
                                    {{ $type['label'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="expiration_date" class="block mb-2 text-sm text-gray-700">تاريخ انتهاء الصلاحية</label>
                        <input type="date" id="expiration_date" name="expiration_date" required
                            value="{{ old('expiration_date') }}"
                            class="w-full h-12 ps-4 border rounded-lg border-slate-200 text-slate-600 focus:border-blue-500 focus:outline-none">
                    </div>

                    <div>
                        <label for="amount" id="amount_label" class="block mb-2 text-sm text-gray-700">{{ __('Quantity') }}</label>
                        <div class="flex rounded-lg border border-slate-200 overflow-hidden focus-within:border-blue-500">
                            <input type="number" min="0" step="1" id="amount" name="amount" required
                                value="{{ old('amount', 0) }}"
                                class="w-full h-12 px-4 text-right text-slate-600 focus:outline-none"
                                placeholder="0">
                            <span id="amount_unit"
                                class="inline-flex items-center px-4 bg-slate-50 text-sm text-slate-600 border-r border-slate-200">{{ __('Unit') }}</span>
                        </div>
                    </div>

                    <div>
                        <label for="location_id" class="block mb-2 text-sm text-gray-700">مكان الدواء</label>
                        <select id="location_id" name="location_id" required
                            class="w-full h-12 ps-4 border rounded-lg border-slate-200 text-slate-600 focus:border-blue-500 focus:outline-none">
                            <option value="">-- اختر المكان --</option>
                            @foreach ($locations as $location)
                                <option value="{{ $location->LocationID }}"
                                    {{ old('location_id') == $location->LocationID ? 'selected' : '' }}>
                                    {{ $location->LocationName }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="notes" class="block mb-2 text-sm text-gray-700">{{ __('Notes') }}</label>
                        <textarea id="notes" name="notes" rows="3"
                            class="w-full px-4 py-3 border rounded-lg text-right border-slate-200 text-slate-600 focus:border-blue-500 focus:outline-none"
                            placeholder="{{ __('Optional') }}">{{ old('notes') }}</textarea>
                    </div>

                    <div class="flex justify-center gap-3">
                        <button type="submit"
                            class="inline-flex items-center justify-center h-12 px-8 text-sm font-medium rounded-full bg-blue-50 text-blue-600 hover:bg-blue-100 transition">{{ __('Add') }}</button>

                        <a href="{{ route('medicine.index') }}"
                            class="inline-flex items-center justify-center h-12 px-8 text-sm font-medium rounded-full bg-gray-100 text-gray-700 hover:bg-gray-200 transition">{{ __('Back') }}</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const types = @json($types);
            const typeSelect = document.getElementById('medicine_type');
            const amountLabel = document.getElementById('amount_label');
            const amountUnit = document.getElementById('amount_unit');

            function updateAmountText() {
                const selected = types[typeSelect.value];
                amountLabel.textContent = selected ? selected.amount_label : 'الكمية';
                amountUnit.textContent = selected ? selected.unit : 'وحدة';
            }

            typeSelect.addEventListener('change', updateAmountText);
            updateAmountText();
        });
    </script>
@endsection
