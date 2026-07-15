@extends('layouts.app', ['pageTitle' => 'حذف دواء'])

@section('content')
    <div class="flex place-content-center">
        <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-md border-2 border-red-300" dir="rtl">
            <div class="mb-6 text-center">
                <h2 class="text-xl font-bold text-red-600">تأكيد حذف الدواء</h2>
            </div>

            <div class="mb-6 text-center">
                <p class="text-gray-700 mb-2">هل أنت متأكد أنك تريد حذف هذا الدواء؟</p>
                <p class="font-bold text-lg text-gray-900">{{ $medicine->MedicineName }}</p>
                <p class="text-sm text-gray-600">تاريخ الانتهاء: {{ $medicine->ExpirationDate }}</p>
            </div>

            @if ($dispenseCount > 0 || $lockCount > 0)
                <div class="mb-6 p-3 rounded-lg bg-yellow-100 text-yellow-800 text-sm text-center">
                    لا يمكن حذف هذا الدواء لأنه موجود في {{ $dispenseCount }} سجل صرف و {{ $lockCount }} سجل حجز.
                </div>

                <div class="flex justify-center">
                    <a href="{{ route('medicine.index') }}"
                        class="inline-flex items-center justify-center h-12 px-6 text-sm font-medium rounded-full bg-gray-200 text-gray-700 hover:bg-gray-300 transition">{{ __('Back') }}</a>
                </div>
            @else
                <form method="POST" action="{{ route('medicine.destroy', $medicine->MedicineID) }}"
                    class="flex justify-center gap-3">
                    @csrf
                    @method('DELETE')

                    <button type="submit"
                        class="inline-flex items-center justify-center h-12 px-6 text-sm font-medium rounded-full bg-red-500 text-white hover:bg-red-600 transition">
                        نعم، احذف
                    </button>

                    <a href="{{ route('medicine.index') }}"
                        class="inline-flex items-center justify-center h-12 px-6 text-sm font-medium rounded-full bg-gray-200 text-gray-700 hover:bg-gray-300 transition">{{ __('Cancel') }}</a>
                </form>
            @endif
        </div>
    </div>
@endsection
