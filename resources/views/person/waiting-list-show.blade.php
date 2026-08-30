@extends('layouts.app', ['pageTitle' => __('Waiting person details')])

@section('content')
    <div class="container mx-auto px-4 py-8">

        {{-- Back Button --}}
        <div class="mb-6">
            <a href="{{ route('person.waiting-list-index') }}"
                class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-gray-900 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 rotate-180" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                {{ __('Back to waiting list') }}
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
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">{{ __('Waiting list') }}</span>
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
                        onsubmit="return confirm(@json(__('Are you sure you want to move this person to the enrolment list?')))">
                        @csrf
                        <button type="submit"
                            class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-lg text-white bg-green-600 hover:bg-green-700 transition-colors">{{ __('Move to enrolment') }}</button>
                    </form>
                    <form method="POST" action="{{ route('person.waiting-list-decline', $person->PersonID) }}"
                        onsubmit="return confirm(@json(__('Are you sure you want to permanently reject and delete this request?')))">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-lg text-white bg-red-600 hover:bg-red-700 transition-colors">
                            {{ __('Reject request') }}
                        </button>
                    </form>
                </div>

            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            {{-- Personal Info --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h2 class="text-base font-semibold text-gray-700 mb-4 flex items-center gap-2">
                    <span class="w-1 h-5 bg-blue-500 rounded-full inline-block"></span>{{ __('Personal information') }}</h2>
                <dl class="space-y-3">
                    <div class="flex justify-between text-sm">
                        <dt class="text-gray-500">{{ __('First name') }}</dt>
                        <dd class="font-medium text-gray-900">{{ $person->FirstName ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between text-sm">
                        <dt class="text-gray-500">{{ __('Second name') }}</dt>
                        <dd class="font-medium text-gray-900">{{ $person->SecondName ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between text-sm">
                        <dt class="text-gray-500">{{ __('Third name') }}</dt>
                        <dd class="font-medium text-gray-900">{{ $person->ThirdName ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between text-sm">
                        <dt class="text-gray-500">{{ __('Fourth name') }}</dt>
                        <dd class="font-medium text-gray-900">{{ $person->FourthName ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between text-sm">
                        <dt class="text-gray-500">{{ __('Gender') }}</dt>
                        <dd class="font-medium text-gray-900">
                            {{ $person->Gender === 'Male' ? __('Male') : ($person->Gender === 'Female' ? __('Female') : '—') }}</dd>
                    </div>
                    <div class="flex justify-between text-sm">
                        <dt class="text-gray-500">{{ __('Date of birth') }}</dt>
                        <dd class="font-medium text-gray-900">{{ $person->DateOfBirth ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between text-sm">
                        <dt class="text-gray-500">{{ __('National ID') }}</dt>
                        <dd class="font-mono font-medium text-gray-900">{{ $person->RaqamQawmy ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between text-sm">
                        <dt class="text-gray-500">{{ __('Scout joining year') }}</dt>
                        <dd class="font-medium text-gray-900">{{ $person->ScoutJoiningYear ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between text-sm">
                        <dt class="text-gray-500">{{ __('Blood type') }}</dt>
                        <dd class="font-medium text-gray-900">{{ $person->BloodTypeName ?? '—' }}</dd>
                    </div>
                </dl>
            </div>

            {{-- Contact Info --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h2 class="text-base font-semibold text-gray-700 mb-4 flex items-center gap-2">
                    <span class="w-1 h-5 bg-green-500 rounded-full inline-block"></span>
                    {{ __('Contact details') }}
                </h2>
                <dl class="space-y-3">
                    <div class="flex justify-between text-sm">
                        <dt class="text-gray-500">{{ __('Personal mobile') }}</dt>
                        <dd class="font-mono font-medium text-gray-900">{{ $person->PersonPersonalMobileNumber ?? '—' }}
                        </dd>
                    </div>
                    <div class="flex justify-between text-sm">
                        <dt class="text-gray-500">{{ __('Father mobile') }}</dt>
                        <dd class="font-mono font-medium text-gray-900">{{ $person->FatherMobileNumber ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between text-sm">
                        <dt class="text-gray-500">{{ __('Mother mobile') }}</dt>
                        <dd class="font-mono font-medium text-gray-900">{{ $person->MotherMobileNumber ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between text-sm">
                        <dt class="text-gray-500">{{ __('Home phone') }}</dt>
                        <dd class="font-mono font-medium text-gray-900">{{ $person->HomePhoneNumber ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between text-sm">
                        <dt class="text-gray-500">{{ __('WhatsApp?') }}</dt>
                        <dd class="font-medium text-gray-900">
                            {{ $person->IsOPersonalPhoneNumberHavingWhatsapp ? __('Yes') : __('No') }}</dd>
                    </div>
                    <div class="flex justify-between text-sm">
                        <dt class="text-gray-500">{{ __('Email') }}</dt>
                        <dd class="font-medium text-gray-900 break-all">{{ $person->PersonalEmail ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between text-sm">
                        <dt class="text-gray-500">{{ __('Facebook') }}</dt>
                        <dd class="font-medium text-gray-900 break-all">
                            @if ($href = \App\Support\SafeHttpUrl::sanitize($person->FacebookProfileURL ?? null))
                                <a href="{{ $href }}" target="_blank"
                                    class="text-blue-600 hover:underline">{{ __('Page link') }}</a>
                            @else
                                —
                            @endif
                        </dd>
                    </div>
                    <div class="flex justify-between text-sm">
                        <dt class="text-gray-500">{{ __('Instagram') }}</dt>
                        <dd class="font-medium text-gray-900 break-all">
                            @if ($href = \App\Support\SafeHttpUrl::sanitize($person->InstagramProfileURL ?? null))
                                <a href="{{ $href }}" target="_blank"
                                    class="text-pink-600 hover:underline">{{ __('Page link') }}</a>
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
                    <span class="w-1 h-5 bg-purple-500 rounded-full inline-block"></span>{{ __('Address') }}</h2>
                <dl class="space-y-3">
                    <div class="flex justify-between text-sm">
                        <dt class="text-gray-500">{{ __('Area') }}</dt>
                        <dd class="font-medium text-gray-900">{{ $person->ManteqaName ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between text-sm">
                        <dt class="text-gray-500">{{ __('District') }}</dt>
                        <dd class="font-medium text-gray-900">{{ $person->DistrictName ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between text-sm">
                        <dt class="text-gray-500">{{ __('Main street') }}</dt>
                        <dd class="font-medium text-gray-900">{{ $person->MainStreetName ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between text-sm">
                        <dt class="text-gray-500">{{ __('Side street') }}</dt>
                        <dd class="font-medium text-gray-900">{{ $person->SubStreetName ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between text-sm">
                        <dt class="text-gray-500">{{ __('Building / floor / apartment') }}</dt>
                        <dd class="font-medium text-gray-900">
                            {{ $person->BuildingNumber ?? '—' }} / {{ $person->FloorNumber ?? '—' }} /
                            {{ $person->AppartmentNumber ?? '—' }}
                        </dd>
                    </div>
                    <div class="flex justify-between text-sm">
                        <dt class="text-gray-500">{{ __('Nearest landmark') }}</dt>
                        <dd class="font-medium text-gray-900">{{ $person->NearestLandmark ?? '—' }}</dd>
                    </div>
                </dl>
            </div>

            {{-- Education & Spiritual --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h2 class="text-base font-semibold text-gray-700 mb-4 flex items-center gap-2">
                    <span class="w-1 h-5 bg-orange-400 rounded-full inline-block"></span>
                    {{ __('Education and spiritual father') }}
                </h2>
                <dl class="space-y-3">
                    <div class="flex justify-between text-sm">
                        <dt class="text-gray-500">{{ __('School') }}</dt>
                        <dd class="font-medium text-gray-900">{{ $person->SchoolName ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between text-sm">
                        <dt class="text-gray-500">{{ __('School graduation year') }}</dt>
                        <dd class="font-medium text-gray-900">{{ $person->SchoolGraduationYear ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between text-sm">
                        <dt class="text-gray-500">{{ __('Faculty') }}</dt>
                        <dd class="font-medium text-gray-900">{{ $person->FacultyName ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between text-sm">
                        <dt class="text-gray-500">{{ __('University') }}</dt>
                        <dd class="font-medium text-gray-900">{{ $person->UniversityName ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between text-sm">
                        <dt class="text-gray-500">{{ __('University graduation year') }}</dt>
                        <dd class="font-medium text-gray-900">{{ $person->UniversityGraduationYear ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between text-sm">
                        <dt class="text-gray-500">{{ __('Spiritual father') }}</dt>
                        <dd class="font-medium text-gray-900">{{ $person->SpiritualFatherName ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between text-sm">
                        <dt class="text-gray-500">{{ __('Spiritual father church') }}</dt>
                        <dd class="font-medium text-gray-900">{{ $person->SpiritualFatherChurchName ?? '—' }}</dd>
                    </div>
                </dl>
            </div>

            {{-- Medical --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h2 class="text-base font-semibold text-gray-700 mb-4 flex items-center gap-2">
                    <span class="w-1 h-5 bg-red-400 rounded-full inline-block"></span>
                    {{ __('Medical data') }}
                </h2>
                <dl class="space-y-3">
                    <div class="flex justify-between text-sm">
                        <dt class="text-gray-500">{{ __('Food allergy') }}</dt>
                        <dd class="font-medium text-gray-900">{{ $person->AllergyFood ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between text-sm">
                        <dt class="text-gray-500">{{ __('Medicine allergy') }}</dt>
                        <dd class="font-medium text-gray-900">{{ $person->AllergyMedicine ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between text-sm">
                        <dt class="text-gray-500">{{ __('Diseases') }}</dt>
                        <dd class="font-medium text-gray-900">{{ $person->MedicalDiseases ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between text-sm">
                        <dt class="text-gray-500">{{ __('Medications') }}</dt>
                        <dd class="font-medium text-gray-900">{{ $person->MedicalMedications ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between text-sm">
                        <dt class="text-gray-500">{{ __('Emergency case?') }}</dt>
                        <dd class="font-medium text-gray-900">{{ $person->HasEmergencyCase ? __('Yes') : __('No') }}</dd>
                    </div>
                    @if ($person->HasEmergencyCase)
                        <div class="flex justify-between text-sm">
                            <dt class="text-gray-500">{{ __('Emergency details') }}</dt>
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
                        {{ __('Admission questions') }}
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
