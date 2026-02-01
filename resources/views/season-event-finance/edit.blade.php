@extends('layouts.app', ['pageTitle' => 'تعديل الإعداد المالي'])

@section('content')
    <div class="flex place-content-center">
        <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-md border-2 border-green-300" dir="rtl">

            <!-- Title -->
            <div class="mb-6 text-center">
                <h2 class="text-xl font-bold text-gray-800">
                    تعديل الإعداد المالي
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    {{ $info->SeasonName }} ({{ $info->SeasonYear }}) — {{ $info->EventName }}
                </p>
            </div>

            <form method="POST" action="{{ route('seasonEventFinance.update', $finance->SeasonEventID) }}">
                @csrf

                <div class="space-y-6">

                    <!-- Supported Price -->
                    <div>
                        <label class="block mb-2 text-sm text-gray-700">
                            السعر المدعوم
                        </label>
                        <input type="number" step="0.01" name="supported_price" value="{{ $finance->SupportedPrice }}"
                            required
                            class="w-full h-12 px-4 border rounded-lg text-right border-slate-200 text-slate-600
                                  focus:border-green-500 focus:outline-none">
                    </div>

                    <!-- Actual Max Price -->
                    <div>
                        <label class="block mb-2 text-sm text-gray-700">
                            السعر الأقصى المسموح
                        </label>
                        <input type="number" step="0.01" name="actual_max_price" value="{{ $finance->ActualMaxPrice }}"
                            required
                            class="w-full h-12 px-4 border rounded-lg text-right border-slate-200 text-slate-600
                                  focus:border-green-500 focus:outline-none">
                    </div>

                    <!-- Installments Number -->
                    <div>
                        <label class="block mb-2 text-sm text-gray-700">
                            عدد الأقساط
                        </label>
                        <input type="number" name="installments_number" min="1"
                            value="{{ $finance->InstallmentsNumber }}" required
                            class="w-full h-12 px-4 border rounded-lg text-right border-slate-200 text-slate-600
                                  focus:border-green-500 focus:outline-none">
                    </div>

                    <!-- Submit -->
                    <div class="flex justify-center gap-4">
                        <button type="submit"
                            class="inline-flex items-center justify-center h-12 px-8 text-sm font-medium
                                   rounded-full bg-green-50 text-green-600 hover:bg-green-100
                                   transition">
                            حفظ التعديلات
                        </button>

                        <a href="{{ route('seasonEventFinance.index') }}"
                            class="inline-flex items-center justify-center h-12 px-8 text-sm font-medium
                              rounded-full bg-gray-100 text-gray-600 hover:bg-gray-200
                              transition">
                            إلغاء
                        </a>
                    </div>

                </div>
            </form>
        </div>
    </div>
@endsection
