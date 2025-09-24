@extends('layouts.app', ['pageTitle' => 'حذف رابط الوسائط'])

@section('content')
    <div class="flex place-content-center">
        <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-md border-2 border-red-300" dir="rtl">
            <!-- Title -->
            <div class="mb-6 text-center">
                <h2 class="text-xl font-bold text-red-600">تأكيد حذف رابط الوسائط</h2>
            </div>



            <!-- Media Info to be deleted -->
            <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                <h3 class="text-sm font-semibold text-red-700 mb-3">معلومات الرابط المراد حذفه:</h3>
                <div class="space-y-2">
                    <p class="text-sm text-gray-700">
                        <strong>الموسم:</strong> {{ $seasonEvent->SeasonName }} ({{ $seasonEvent->SeasonYear }})
                    </p>
                    <p class="text-sm text-gray-700">
                        <strong>الفعالية:</strong> {{ $seasonEvent->EventName }}
                    </p>
                    <p class="text-sm text-gray-700 break-all">
                        <strong>الرابط:</strong>
                        <a href="{{ $media->DriveLink }}" target="_blank" class="text-blue-500 hover:underline">
                            {{ $media->DriveLink }}
                        </a>
                    </p>
                </div>
            </div>




            <!-- Confirmation Form -->
            <form method="POST" action="{{ route('media.destroy', $media->MediaID) }}" class="mb-4">
                @csrf
                @method('DELETE')

                <div class="flex justify-between gap-4">
                    <a href="{{ route('media.index') }}"
                        class="inline-flex items-center justify-center h-12 px-8 text-sm font-medium tracking-wide rounded-full bg-gray-50 text-gray-500 hover:bg-gray-100 hover:text-gray-600 transition flex-1">
                        إلغاء
                    </a>
                    <button type="submit"
                        class="inline-flex items-center justify-center h-12 px-8 text-sm font-medium tracking-wide rounded-full bg-red-500 text-white hover:bg-red-600 transition flex-1">
                        حذف نهائياً
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
