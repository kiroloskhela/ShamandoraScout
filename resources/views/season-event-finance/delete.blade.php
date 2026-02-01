@extends('layouts.app', ['pageTitle' => 'حذف الإعداد المالي'])

@section('content')
    <div class="flex place-content-center">
        <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-md border-2 border-red-300" dir="rtl">

            <!-- Title -->
            <div class="mb-6 text-center">
                <h2 class="text-xl font-bold text-red-700">
                    تأكيد الحذف
                </h2>
                <p class="text-sm text-gray-600 mt-2">
                    هل أنت متأكد من حذف الإعداد المالي لهذه الفعالية؟
                </p>
            </div>

            <!-- Info -->
            <div class="bg-gray-50 p-4 rounded-lg mb-6 text-sm text-gray-700">
                <p><strong>الموسم:</strong> {{ $info->SeasonName }} ({{ $info->SeasonYear }})</p>
                <p class="mt-1"><strong>الفعالية:</strong> {{ $info->EventName }}</p>
            </div>

            <!-- Actions -->
            <form method="POST" action="{{ route('seasonEventFinance.destroy', $id) }}">
                @csrf

                <div class="flex justify-center gap-4">
                    <button type="submit"
                        class="inline-flex items-center justify-center h-12 px-8 text-sm font-medium
                               rounded-full bg-red-600 text-white hover:bg-red-700
                               transition">
                        نعم، احذف
                    </button>

                    <a href="{{ route('seasonEventFinance.index') }}"
                        class="inline-flex items-center justify-center h-12 px-8 text-sm font-medium
                          rounded-full bg-gray-100 text-gray-600 hover:bg-gray-200
                          transition">
                        إلغاء
                    </a>
                </div>
            </form>

        </div>
    </div>
@endsection
