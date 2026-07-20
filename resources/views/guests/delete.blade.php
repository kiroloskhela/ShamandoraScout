@extends('layouts.app', ['pageTitle' => __('Delete guest')])

@section('content')
    <div class="flex place-content-center">
        <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-md border-2 border-red-300">
            <div class="mb-6 text-center">
                <h2 class="text-xl font-bold text-red-600">تأكيد حذف الضيف</h2>
            </div>

            <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                <h3 class="text-sm font-semibold text-red-700 mb-3">معلومات الضيف المراد حذفه:</h3>
                <div class="space-y-2">
                    <p class="text-sm text-gray-700">
                        <strong>{{ __('Name:') }}</strong> {{ $guest->FullName }}
                    </p>
                    <p class="text-sm text-gray-700">
                        <strong>رقم الضيف:</strong> {{ $guest->GuestID }}
                    </p>
                </div>
            </div>

            <form method="POST" action="{{ route('guests.destroy', $guest->GuestID) }}" class="mb-4">
                @csrf

                <div class="flex justify-between gap-4">
                    <a href="{{ route('guests.index') }}"
                        class="inline-flex items-center justify-center h-12 px-8 text-sm font-medium tracking-wide rounded-full bg-gray-50 text-gray-500 hover:bg-gray-100 hover:text-gray-600 transition flex-1">{{ __('Cancel') }}</a>
                    <button type="submit"
                        class="inline-flex items-center justify-center h-12 px-8 text-sm font-medium tracking-wide rounded-full bg-red-500 text-white hover:bg-red-600 transition flex-1">
                        حذف نهائياً
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
