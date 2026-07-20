@extends('layouts.app', ['pageTitle' => __('Delete family member')])

@section('content')
    <div class="flex place-content-center">
        <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-md border-2 border-red-300">
            <div class="mb-6 text-center">
                <h2 class="text-xl font-bold text-red-600">تأكيد حذف فرد الأسرة</h2>
            </div>

            <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                <h3 class="text-sm font-semibold text-red-700 mb-3">معلومات فرد الأسرة المراد حذفه:</h3>
                <div class="space-y-2">
                    <p class="text-sm text-gray-700">
                        <strong>{{ __('Name:') }}</strong> {{ $familyMember->FullName }}
                    </p>
                    <p class="text-sm text-gray-700">
                        <strong>عدد الروابط:</strong> {{ $assignmentsCount }}
                    </p>
                </div>
            </div>

            <form method="POST" action="{{ route('family-members.destroy', $familyMember->FamilyID) }}" class="mb-4">
                @csrf

                <div class="flex justify-between gap-4">
                    <a href="{{ route('family-members.index') }}"
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
