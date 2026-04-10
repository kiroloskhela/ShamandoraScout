@extends('layouts.app', ['pageTitle' => 'عرض بيانات ضيف'])

@section('content')
    <div class="flex place-content-center">
        <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-5xl border-2 border-blue-300" dir="rtl">
            <div class="mb-6 text-center">
                <h2 class="text-xl font-bold text-gray-800">بيانات الضيف</h2>
            </div>

            <div class="mb-8">
                <h3 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2">البيانات الأساسية</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="p-4 bg-gray-50 rounded-lg">
                        <p class="text-sm text-gray-500 mb-1">الاسم</p>
                        <p class="font-medium text-gray-800">{{ $guest->FullName }}</p>
                    </div>

                    <div class="p-4 bg-gray-50 rounded-lg">
                        <p class="text-sm text-gray-500 mb-1">البريد الإلكتروني</p>
                        <p class="font-medium text-gray-800">{{ $guest->Email ?? '-' }}</p>
                    </div>

                    <div class="p-4 bg-gray-50 rounded-lg">
                        <p class="text-sm text-gray-500 mb-1">رقم الموبايل</p>
                        <p class="font-medium text-gray-800">{{ $guest->MobileNumber ?? '-' }}</p>
                    </div>

                    <div class="p-4 bg-gray-50 rounded-lg">
                        <p class="text-sm text-gray-500 mb-1">تاريخ الميلاد</p>
                        <p class="font-medium text-gray-800">{{ $guest->DateOfBirth ?? '-' }}</p>
                    </div>

                    <div class="p-4 bg-gray-50 rounded-lg">
                        <p class="text-sm text-gray-500 mb-1">الرقم القومي</p>
                        <p class="font-medium text-gray-800">{{ $guest->RaqamQawmy ?? '-' }}</p>
                    </div>

                    <div class="p-4 bg-gray-50 rounded-lg">
                        <p class="text-sm text-gray-500 mb-1">الشخص المرتبط</p>
                        <p class="font-medium text-blue-700">{{ $guest->PersonFullName ?? '-' }}</p>
                    </div>
                </div>
            </div>

            <div class="flex justify-between gap-4 mt-8">
                <a href="{{ route('guests.index') }}"
                    class="inline-flex items-center justify-center h-12 px-8 text-sm font-medium tracking-wide rounded-full bg-gray-50 text-gray-500 hover:bg-gray-100 hover:text-gray-600 transition">
                    رجوع
                </a>

                <a href="{{ route('guests.edit', $guest->GuestID) }}"
                    class="inline-flex items-center justify-center h-12 px-8 text-sm font-medium tracking-wide rounded-full bg-emerald-50 text-emerald-500 hover:bg-emerald-100 hover:text-emerald-600 transition">
                    تعديل
                </a>
            </div>
        </div>
    </div>
@endsection
