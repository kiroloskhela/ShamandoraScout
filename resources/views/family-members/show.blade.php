@extends('layouts.app', ['pageTitle' => 'عرض بيانات فرد أسرة'])

@section('content')
    <div class="flex place-content-center">
        <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-5xl border-2 border-blue-300" dir="rtl">
            <div class="mb-6 text-center">
                <h2 class="text-xl font-bold text-gray-800">بيانات فرد الأسرة</h2>
            </div>

            <div class="mb-8">
                <h3 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2">{{ __('Basic information') }}</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="p-4 bg-gray-50 rounded-lg">
                        <p class="text-sm text-gray-500 mb-1">{{ __('Name') }}</p>
                        <p class="font-medium text-gray-800">{{ $familyMember->FullName }}</p>
                    </div>

                    <div class="p-4 bg-gray-50 rounded-lg">
                        <p class="text-sm text-gray-500 mb-1">{{ __('Email') }}</p>
                        <p class="font-medium text-gray-800">{{ $familyMember->Email ?? '-' }}</p>
                    </div>

                    <div class="p-4 bg-gray-50 rounded-lg">
                        <p class="text-sm text-gray-500 mb-1">{{ __('Mobile number') }}</p>
                        <p class="font-medium text-gray-800">{{ $familyMember->MobileNumber ?? '-' }}</p>
                    </div>

                    <div class="p-4 bg-gray-50 rounded-lg">
                        <p class="text-sm text-gray-500 mb-1">{{ __('Date of birth') }}</p>
                        <p class="font-medium text-gray-800">{{ $familyMember->DateOfBirth ?? '-' }}</p>
                    </div>

                    <div class="p-4 bg-gray-50 rounded-lg md:col-span-2">
                        <p class="text-sm text-gray-500 mb-1">{{ __('National ID') }}</p>
                        <p class="font-medium text-gray-800">{{ $familyMember->RaqamQawmy ?? '-' }}</p>
                    </div>
                </div>
            </div>

            <div class="mb-8">
                <h3 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2">الروابط مع الأشخاص</h3>

                @if ($assignments->count() > 0)
                    <div class="space-y-4">
                        @foreach ($assignments as $assignment)
                            <div class="p-4 bg-gray-50 rounded-lg border border-slate-200">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-sm text-gray-500 mb-1">{{ __('Person') }}</p>
                                        <p class="font-medium text-gray-800">{{ $assignment->PersonFullName }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500 mb-1">{{ __('Relationship') }}</p>
                                        <p class="font-medium text-blue-700">{{ $assignment->RelationName }}</p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg text-sm text-yellow-700">
                        لا يوجد أي ربط لهذا الفرد حاليًا.
                    </div>
                @endif
            </div>

            <div class="flex justify-between gap-4 mt-8">
                <a href="{{ route('family-members.index') }}"
                    class="inline-flex items-center justify-center h-12 px-8 text-sm font-medium tracking-wide rounded-full bg-gray-50 text-gray-500 hover:bg-gray-100 hover:text-gray-600 transition">{{ __('Back') }}</a>

                <a href="{{ route('family-members.edit', $familyMember->FamilyID) }}"
                    class="inline-flex items-center justify-center h-12 px-8 text-sm font-medium tracking-wide rounded-full bg-emerald-50 text-emerald-500 hover:bg-emerald-100 hover:text-emerald-600 transition">{{ __('Edit') }}</a>
            </div>
        </div>
    </div>
@endsection
