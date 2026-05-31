@extends('layouts.app', ['pageTitle' => 'بيانات المنتظر'])

@section('content')
    <div class="container mx-auto px-4 py-8" dir="rtl">

        {{-- Back Button --}}
        <div class="mb-6">
            <a href="{{ route('person.waiting-list-index') }}"
                class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-gray-900 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 rotate-180" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                العودة إلى قائمة الانتظار
            </a>
        </div>

        {{-- Header Card --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">

                {{-- Avatar --}}
                <div
                    class="w-20 h-20 rounded-full bg-yellow-100 flex items-center justify-center text-yellow-600 text-2xl font-bold shrink-0">
                    {{ mb_substr($person->FirstName ?? '؟', 0, 1) }}
                </div>

                <div class="flex-1">
                    <div class="flex flex-wrap items-center gap-2 mb-1">
                        <h1 class="text-2xl font-bold text-gray-900">
                            {{ trim(($person->FirstName ?? '') . ' ' . ($person->SecondName ?? '') . ' ' . ($person->ThirdName ?? '') . ' ' . ($person->FourthName ?? '')) }}
                        </h1>
                        <span
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                            قائمة الانتظار
                        </span>
                    </div>
                    <p class="text-sm text-gray-500">
                        {{ $person->QetaaName ?? '—' }} &bull; {{ $person->SanaMarhalaName ?? '—' }}
                    </p>
                    @if ($person->ShamandoraCode ?? false)
                        <p class="text-xs text-gray-400 mt-1 font-mono">{{ $person->ShamandoraCode }}</p>
                    @endif
                </div>

                {{-- Action Buttons --}}
                <div class="flex flex-wrap gap-2 shrink-0">
                    <form method="POST" action="{{ route('person.waiting-list-migrate', $person->PersonID) }}"
                        onsubmit="return confirm('هل أنت متأكد من نقل هذا الشخص إلى قائمة التسجيل؟')">
                        @csrf
                        <button type="submit"
                            class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-lg text-white bg-green-600 hover:bg-green-700 transition-colors">
                            نقل للتسجيل
                        </button>
                    </form>
                    <form method="POST" action="{{ route('person.waiting-list-decline', $person->PersonID) }}"
                        onsubmit="return confirm('هل أنت متأكد من رفض وحذف هذا الطلب نهائياً؟')">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-lg text-white bg-red-600 hover:bg-red-700 transition-colors">
                            رفض الطلب
                        </button>
                    </form>
                </div>

            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            {{-- Personal Info --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h2 class="text-base font-semibold text-gray-700 mb-4 flex items-center gap-2">
                    <span class="w-1 h-5 bg-blue-500 rounded-full inline-block"></span>
                    البيانات الشخصية
                </h2>
                <dl class="space-y-3">
                    <div class="flex justify-between text-sm">
                        <dt class="text-gray-500">الاسم الأول</dt>
                        <dd class="font-medium text-gray-900">{{ $person->FirstName ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between text-sm">
                        <dt class="text-gray-500">الاسم الثاني</dt>
                        <dd class="font-medium text-gray-900">{{ $person->SecondName ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between text-sm">
                        <dt class="text-gray-500">الاسم الثالث</dt>
                        <dd class="font-medium text-gray-900">{{ $person->ThirdName ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between text-sm">
                        <dt class="text-gray-500">الاسم الرابع</dt>
                        <dd class="font-medium text-gray-900">{{ $person->FourthName ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between text-sm">
                        <dt class="text-gray-500">الجنس</dt>
                        <dd class="font-medium text-gray-900">
                            {{ $person->Gender === 'Male' ? 'ذكر' : ($person->Gender === 'Female' ? 'أنثى' : '—') }}</dd>
                    </div>
                    <div class="flex justify-between text-sm">
                        <dt class="text-gray-500">تاريخ الميلاد</dt>
                        <dd class="font-medium text-gray-900">{{ $person->DateOfBirth ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between text-sm">
                        <dt class="text-gray-500">الرقم القومي</dt>
                        <dd class="font-mono font-medium text-gray-900">{{ $person->RaqamQawmy ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between text-sm">
                        <dt class="text-gray-500">سنة الانضمام الكشفي</dt>
                        <dd class="font-medium text-gray-900">{{ $person->ScoutJoiningYear ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between text-sm">
                        <dt class="text-gray-500">فصيلة الدم</dt>
                        <dd class="font-medium text-gray-900">{{ $person->BloodTypeName ?? '—' }}</dd>
                    </div>
                </dl>
            </div>

            {{-- Contact Info --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h2 class="text-base font-semibold text-gray-700 mb-4 flex items-center gap-2">
                    <span class="w-1 h-5 bg-green-500 rounded-full inline-block"></span>
                    بيانات التواصل
                </h2>
                <dl class="space-y-3">
                    <div class="flex justify-between text-sm">
                        <dt class="text-gray-500">موبايل شخصي</dt>
                        <dd class="font-mono font-medium text-gray-900">{{ $person->PersonPersonalMobileNumber ?? '—' }}
                        </dd>
                    </div>
                    <div class="flex justify-between text-sm">
                        <dt class="text-gray-500">موبايل الأب</dt>
                        <dd class="font-mono font-medium text-gray-900">{{ $person->FatherMobileNumber ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between text-sm">
                        <dt class="text-gray-500">موبايل الأم</dt>
                        <dd class="font-mono font-medium text-gray-900">{{ $person->MotherMobileNumber ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between text-sm">
                        <dt class="text-gray-500">تليفون المنزل</dt>
                        <dd class="font-mono font-medium text-gray-900">{{ $person->HomePhoneNumber ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between text-sm">
                        <dt class="text-gray-500">واتساب؟</dt>
                        <dd class="font-medium text-gray-900">
                            {{ $person->IsOPersonalPhoneNumberHavingWhatsapp ? 'نعم' : 'لا' }}</dd>
                    </div>
                    <div class="flex justify-between text-sm">
                        <dt class="text-gray-500">البريد الإلكتروني</dt>
                        <dd class="font-medium text-gray-900 break-all">{{ $person->PersonalEmail ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between text-sm">
                        <dt class="text-gray-500">فيسبوك</dt>
                        <dd class="font-medium text-gray-900 break-all">
                            @if ($person->FacebookProfileURL ?? false)
                                <a href="{{ $person->FacebookProfileURL }}" target="_blank"
                                    class="text-blue-600 hover:underline">رابط الصفحة</a>
                            @else
                                —
                            @endif
                        </dd>
                    </div>
                    <div class="flex justify-between text-sm">
                        <dt class="text-gray-500">إنستجرام</dt>
                        <dd class="font-medium text-gray-900 break-all">
                            @if ($person->InstagramProfileURL ?? false)
                                <a href="{{ $person->InstagramProfileURL }}" target="_blank"
                                    class="text-pink-600 hover:underline">رابط الصفحة</a>
                            @else
                                —
                            @endif
                        </dd>
                    </div>
                </dl>
            </div>

            {{-- Address --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h2 class="text-base font-semibold text-gray-700 mb-4 flex items-center gap-2">
                    <span class="w-1 h-5 bg-purple-500 rounded-full inline-block"></span>
                    العنوان
                </h2>
                <dl class="space-y-3">
                    <div class="flex justify-between text-sm">
                        <dt class="text-gray-500">المنطقة</dt>
                        <dd class="font-medium text-gray-900">{{ $person->ManteqaName ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between text-sm">
                        <dt class="text-gray-500">الحي</dt>
                        <dd class="font-medium text-gray-900">{{ $person->DistrictName ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between text-sm">
                        <dt class="text-gray-500">الشارع الرئيسي</dt>
                        <dd class="font-medium text-gray-900">{{ $person->MainStreetName ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between text-sm">
                        <dt class="text-gray-500">الشارع الفرعي</dt>
                        <dd class="font-medium text-gray-900">{{ $person->SubStreetName ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between text-sm">
                        <dt class="text-gray-500">المبنى / الدور / الشقة</dt>
                        <dd class="font-medium text-gray-900">
                            {{ $person->BuildingNumber ?? '—' }} / {{ $person->FloorNumber ?? '—' }} /
                            {{ $person->AppartmentNumber ?? '—' }}
                        </dd>
                    </div>
                    <div class="flex justify-between text-sm">
                        <dt class="text-gray-500">أقرب معلم</dt>
                        <dd class="font-medium text-gray-900">{{ $person->NearestLandmark ?? '—' }}</dd>
                    </div>
                </dl>
            </div>

            {{-- Education & Spiritual --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h2 class="text-base font-semibold text-gray-700 mb-4 flex items-center gap-2">
                    <span class="w-1 h-5 bg-orange-400 rounded-full inline-block"></span>
                    التعليم والأب الروحي
                </h2>
                <dl class="space-y-3">
                    <div class="flex justify-between text-sm">
                        <dt class="text-gray-500">المدرسة</dt>
                        <dd class="font-medium text-gray-900">{{ $person->SchoolName ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between text-sm">
                        <dt class="text-gray-500">سنة تخرج المدرسة</dt>
                        <dd class="font-medium text-gray-900">{{ $person->SchoolGraduationYear ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between text-sm">
                        <dt class="text-gray-500">الكلية</dt>
                        <dd class="font-medium text-gray-900">{{ $person->FacultyName ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between text-sm">
                        <dt class="text-gray-500">الجامعة</dt>
                        <dd class="font-medium text-gray-900">{{ $person->UniversityName ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between text-sm">
                        <dt class="text-gray-500">سنة تخرج الجامعة</dt>
                        <dd class="font-medium text-gray-900">{{ $person->UniversityGraduationYear ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between text-sm">
                        <dt class="text-gray-500">الأب الروحي</dt>
                        <dd class="font-medium text-gray-900">{{ $person->SpiritualFatherName ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between text-sm">
                        <dt class="text-gray-500">كنيسة الأب الروحي</dt>
                        <dd class="font-medium text-gray-900">{{ $person->SpiritualFatherChurchName ?? '—' }}</dd>
                    </div>
                </dl>
            </div>

            {{-- Medical --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h2 class="text-base font-semibold text-gray-700 mb-4 flex items-center gap-2">
                    <span class="w-1 h-5 bg-red-400 rounded-full inline-block"></span>
                    البيانات الطبية
                </h2>
                <dl class="space-y-3">
                    <div class="flex justify-between text-sm">
                        <dt class="text-gray-500">حساسية طعام</dt>
                        <dd class="font-medium text-gray-900">{{ $person->AllergyFood ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between text-sm">
                        <dt class="text-gray-500">حساسية دواء</dt>
                        <dd class="font-medium text-gray-900">{{ $person->AllergyMedicine ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between text-sm">
                        <dt class="text-gray-500">أمراض</dt>
                        <dd class="font-medium text-gray-900">{{ $person->MedicalDiseases ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between text-sm">
                        <dt class="text-gray-500">أدوية</dt>
                        <dd class="font-medium text-gray-900">{{ $person->MedicalMedications ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between text-sm">
                        <dt class="text-gray-500">حالة طوارئ؟</dt>
                        <dd class="font-medium text-gray-900">{{ $person->HasEmergencyCase ? 'نعم' : 'لا' }}</dd>
                    </div>
                    @if ($person->HasEmergencyCase)
                        <div class="flex justify-between text-sm">
                            <dt class="text-gray-500">تفاصيل الطوارئ</dt>
                            <dd class="font-medium text-gray-900">{{ $person->EmergencyDetails ?? '—' }}</dd>
                        </div>
                    @endif
                </dl>
            </div>

            {{-- Entry Questions --}}
            @if ($questions->isNotEmpty())
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 lg:col-span-2">
                    <h2 class="text-base font-semibold text-gray-700 mb-4 flex items-center gap-2">
                        <span class="w-1 h-5 bg-indigo-500 rounded-full inline-block"></span>
                        أسئلة القبول
                    </h2>
                    <dl class="space-y-4">
                        @foreach ($questions as $q)
                            <div class="text-sm border-b border-gray-50 pb-3 last:border-0 last:pb-0">
                                <dt class="text-gray-500 mb-1">{{ $q->QuestionText }}</dt>
                                <dd class="font-medium text-gray-900">{{ $q->Answer ?? '—' }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </div>
            @endif

        </div>
    </div>
@endsection
