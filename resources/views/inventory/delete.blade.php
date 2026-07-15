@extends('layouts.app', ['pageTitle' => "حذف عنصر من المخزون"])

@section('content')
<div class="flex place-content-center">
    <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-md border-2 border-red-300" dir="rtl">
        <!-- Title -->
        <div class="mb-6 text-center">
            <h2 class="text-xl font-bold text-red-600">تأكيد حذف العنصر</h2>
        </div>

        <!-- Item Info -->
        <div class="mb-6 text-center">
            <p class="text-gray-700 mb-2">هل أنت متأكد أنك تريد حذف هذا العنصر؟</p>
            <p class="font-bold text-lg text-gray-900">{{ $item->ItemName }}</p>
            <p class="text-sm text-gray-600">
                الكمية: {{ $item->ItemQuantity ?? '—' }} 
                {{ $item->ItemMeasuringUnit ?? '' }}
            </p>
        </div>

        <!-- Form -->
        <form method="POST" action="{{ route('inventory.destroy', $item->InventoryID) }}" class="flex justify-center space-x-4">
            @csrf
            @method('DELETE')

            <button type="submit"
                class="inline-flex items-center justify-center h-12 px-6 text-sm font-medium rounded-full bg-red-500 text-white hover:bg-red-600 transition">
                نعم، احذف
            </button>

            <a href="{{ route('inventory.index') }}"
                class="inline-flex items-center justify-center h-12 px-6 text-sm font-medium rounded-full bg-gray-200 text-gray-700 hover:bg-gray-300 transition">{{ __('Cancel') }}</a>
        </form>
    </div>
</div>
@endsection
