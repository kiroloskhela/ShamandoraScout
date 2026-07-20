@extends('layouts.app', ['pageTitle' => __('Add new person')])

@section('content')
    @php
        $inputClass = 'w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500';
        $selectClass = $inputClass;
        $labelClass = 'block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1';
    @endphp

    <style>
        .person-form-page input[type="text"],
        .person-form-page input[type="email"],
        .person-form-page input[type="date"],
        .person-form-page input[type="number"],
        .person-form-page input[type="url"],
        .person-form-page select,
        .person-form-page textarea {
            min-height: 50px;
        }
    </style>

    <div class="person-form-page py-8">
        @if (session('status'))
            <div class="max-w-6xl mx-auto px-4 mb-4">
                <div class="rounded-xl bg-emerald-600 text-white px-5 py-4 shadow">
                    {{ session('status') }}
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('person.insert') }}" id="regForm">
            @csrf

            <input type="hidden" name="RequestPersonID" value="{{ Auth()->user()->PersonID }}">

            <div class="max-w-6xl mx-auto px-4">
                <div class="rounded-3xl bg-white dark:bg-slate-900 shadow-xl ring-1 ring-slate-200 dark:ring-slate-700 overflow-hidden">

                    <div class="px-6 md:px-10 py-8 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60">
                        <div class="flex flex-col items-center justify-center gap-4 text-center">
                            <img src="{{ asset('img/shamandora.webp') }}" alt="Logo" class="h-14 w-14 object-contain dark:hidden" />
                            <img src="{{ asset('img/shamandora-dark.webp') }}" alt="Logo" class="h-14 w-14 object-contain hidden dark:block" />
                            <div>
                                <h1 class="text-2xl md:text-3xl font-bold text-slate-900 dark:text-slate-100">{{ __('Enter new member data') }}</h1>
                                <p class="text-slate-500 dark:text-slate-400 mt-2">{{ __('Fill in all enrollee data to continue to sector questions.') }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="p-6 md:p-10 space-y-8">

                        <section class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-5 md:p-6">
                            <div class="flex items-start justify-between gap-4 mb-5">
                                <div>
                                    <h2 class="text-xl font-bold text-slate-900 dark:text-slate-100">{{ __('Part 1: Personal information') }}</h2>
                                    <p class="text-slate-500 dark:text-slate-400 mt-1 text-sm">{{ __('Enter the enrollee\'s basic personal information.') }}</p>
                                </div>
                                <span class="shrink-0 inline-flex items-center rounded-full bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 px-4 py-2 text-sm font-semibold">1 / 4</span>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                                <div class="md:col-span-3">
                                    <label for="first_name" class="{{ $labelClass }}">{{ __('First name') }}</label>
                                    <input type="text" name="first_name" id="first_name" class="{{ $inputClass }}" required>
                                </div>

                                <div class="md:col-span-3">
                                    <label for="second_name" class="{{ $labelClass }}">{{ __('Second name') }}</label>
                                    <input type="text" name="second_name" id="second_name" class="{{ $inputClass }}" required>
                                </div>

                                <div class="md:col-span-3">
                                    <label for="third_name" class="{{ $labelClass }}">{{ __('Third name') }}</label>
                                    <input type="text" name="third_name" id="third_name" class="{{ $inputClass }}" required>
                                </div>

                                <div class="md:col-span-3">
                                    <label for="fourth_name" class="{{ $labelClass }}">{{ __('Fourth name') }}</label>
                                    <input type="text" name="fourth_name" id="fourth_name" class="{{ $inputClass }}">
                                </div>

                                <div class="md:col-span-3">
                                    <label for="gender" class="{{ $labelClass }}">{{ __('Applicant gender') }} **</label>
                                    <select name="gender" id="gender" class="{{ $selectClass }}" required>
                                        <option value="" disabled selected>{{ __('Choose gender') }}</option>
                                        <option value="Male">{{ __('Male') }}</option>
                                        <option value="Female">{{ __('Female') }}</option>
                                    </select>
                                </div>

                                <div class="md:col-span-6">
                                    <label for="email_input" class="{{ $labelClass }}">{{ __('Email') }}</label>
                                    <input type="email" name="email_input" id="email_input" class="{{ $inputClass }}" dir="ltr">
                                </div>

                                <div class="md:col-span-6">
                                    <label for="birthdate_input" class="{{ $labelClass }}">{{ __('Date of birth') }} **</label>
                                    <input type="date" name="birthdate_input" id="birthdate_input" class="{{ $inputClass }}" required>
                                </div>

                                <div class="md:col-span-6">
                                    <label for="joining_year_input" class="{{ $labelClass }}">{{ __('Joining year') }} **</label>
                                    <select name="joining_year_input" id="joining_year_input" class="{{ $selectClass }}" required>
                                        <option value="" disabled selected>{{ __('Choose joining year') }}</option>
                                        @for ($i = 1990; $i <= date('Y'); $i++)
                                            <option value="{{ $i }}">{{ $i }}</option>
                                        @endfor
                                    </select>
                                </div>

                                <div class="md:col-span-6">
                                    <label for="input_raqam_qawmy" class="{{ $labelClass }}">{{ __('National ID') }} **</label>
                                    <input type="number" name="input_raqam_qawmy" id="input_raqam_qawmy" class="{{ $inputClass }}"
                                        placeholder="{{ __('Enter 14-digit national ID') }}" required>
                                </div>

                                <div class="md:col-span-6">
                                    <label for="blood_type_input" class="{{ $labelClass }}">{{ __('Blood type') }} **</label>
                                    <select name="blood_type_input" id="blood_type_input" class="{{ $selectClass }}" required>
                                        <option value="" disabled selected>{{ __('Choose blood type') }}</option>
                                        @foreach ($blood as $blood_element)
                                            <option value="{{ $blood_element->BloodTypeID }}">{{ $blood_element->BloodTypeName }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="md:col-span-6">
                                    <label for="inputFacebookLink" class="{{ $labelClass }}">{{ __('Facebook link (if any)') }}</label>
                                    <input type="text" name="inputFacebookLink" id="inputFacebookLink" class="{{ $inputClass }}" dir="ltr">
                                </div>

                                <div class="md:col-span-12">
                                    <label for="inputInstagramLink" class="{{ $labelClass }}">{{ __('Instagram link (if any)') }}</label>
                                    <input type="text" name="inputInstagramLink" id="inputInstagramLink" class="{{ $inputClass }}" dir="ltr">
                                </div>
                            </div>
                        </section>

                        <section class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-5 md:p-6">
                            <div class="flex items-start justify-between gap-4 mb-5">
                                <div>
                                    <h2 class="text-xl font-bold text-slate-900 dark:text-slate-100">{{ __('Part 2: Contact and address') }}</h2>
                                    <p class="text-slate-500 dark:text-slate-400 mt-1 text-sm">{{ __('Enter contact numbers and home address.') }}</p>
                                </div>
                                <span class="shrink-0 inline-flex items-center rounded-full bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 px-4 py-2 text-sm font-semibold">2 / 4</span>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                                <div class="md:col-span-3">
                                    <label for="personal_phone_number" class="{{ $labelClass }}">{{ __('Personal mobile') }} **</label>
                                    <input type="number" name="personal_phone_number" id="personal_phone_number" class="{{ $inputClass }}" required>
                                </div>

                                <div class="md:col-span-3">
                                    <label for="father_phone_number" class="{{ $labelClass }}">{{ __('Father mobile (if any)') }}</label>
                                    <input type="number" name="father_phone_number" id="father_phone_number" class="{{ $inputClass }}">
                                </div>

                                <div class="md:col-span-3">
                                    <label for="mother_phone_number" class="{{ $labelClass }}">{{ __('Mother mobile (if any)') }}</label>
                                    <input type="text" name="mother_phone_number" id="mother_phone_number" class="{{ $inputClass }}">
                                </div>

                                <div class="md:col-span-3">
                                    <label for="home_phone_number" class="{{ $labelClass }}">{{ __('Landline (if any)') }}</label>
                                    <input type="text" name="home_phone_number" id="home_phone_number" class="{{ $inputClass }}">
                                </div>

                                <div class="md:col-span-4">
                                    <label for="has_whatsapp" class="{{ $labelClass }}">{{ __('Does the primary mobile number have WhatsApp?') }}</label>
                                    <select name="has_whatsapp" id="has_whatsapp" class="{{ $selectClass }}">
                                        <option value="" disabled selected>{{ __('Choose yes or no') }}</option>
                                        <option value="True">{{ __('Yes') }}</option>
                                        <option value="False">{{ __('No') }}</option>
                                    </select>
                                </div>

                                <div class="md:col-span-4">
                                    <label for="building_number" class="{{ $labelClass }}">{{ __('Building number') }} **</label>
                                    <input type="number" name="building_number" id="building_number" class="{{ $inputClass }}" required>
                                </div>

                                <div class="md:col-span-4">
                                    <label for="floor_number" class="{{ $labelClass }}">{{ __('Floor number') }} **</label>
                                    <input type="number" name="floor_number" id="floor_number" class="{{ $inputClass }}" required>
                                </div>

                                <div class="md:col-span-4">
                                    <label for="appartment_number" class="{{ $labelClass }}">{{ __('Apartment number') }} **</label>
                                    <input type="number" name="appartment_number" id="appartment_number" class="{{ $inputClass }}" required>
                                </div>

                                <div class="md:col-span-4">
                                    <label for="sub_street_name" class="{{ $labelClass }}">{{ __('Side street') }} **</label>
                                    <input type="text" name="sub_street_name" id="sub_street_name" class="{{ $inputClass }}" required>
                                </div>

                                <div class="md:col-span-4">
                                    <label for="main_street_name" class="{{ $labelClass }}">{{ __('Main street') }}</label>
                                    <input type="text" name="main_street_name" id="main_street_name" class="{{ $inputClass }}">
                                </div>

                                <div class="md:col-span-12">
                                    <label for="nearest_landmark" class="{{ $labelClass }}">{{ __('Nearest landmark') }}</label>
                                    <input type="text" name="nearest_landmark" id="nearest_landmark" class="{{ $inputClass }}">
                                </div>

                                <div class="md:col-span-6">
                                    <label for="manteqa_id" class="{{ $labelClass }}">{{ __('Area') }} **</label>
                                    <select name="manteqa_id" id="manteqa_id" class="{{ $selectClass }}" required>
                                        <option value="" disabled selected>{{ __('Choose area') }}</option>
                                        @foreach ($manateq as $manteqa)
                                            <option value="{{ $manteqa->ManteqaID }}">{{ $manteqa->ManteqaName }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="md:col-span-6">
                                    <label for="district_id" class="{{ $labelClass }}">{{ __('District') }} **</label>
                                    <select name="district_id" id="district_id" class="{{ $selectClass }}" required>
                                        <option value="" disabled selected>{{ __('Choose district') }}</option>
                                        @foreach ($districts as $district)
                                            <option value="{{ $district->DistrictID }}">{{ $district->DistrictName }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </section>

                        <section class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-5 md:p-6">
                            <div class="flex items-start justify-between gap-4 mb-5">
                                <div>
                                    <h2 class="text-xl font-bold text-slate-900 dark:text-slate-100">{{ __('Part 3: Educational and church data') }}</h2>
                                    <p class="text-slate-500 dark:text-slate-400 mt-1 text-sm">{{ __('Enter educational and church information.') }}</p>
                                </div>
                                <span class="shrink-0 inline-flex items-center rounded-full bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 px-4 py-2 text-sm font-semibold">3 / 4</span>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                                <div class="md:col-span-12">
                                    <label for="sana_marhala_id" class="{{ $labelClass }}">{{ __('Year and study stage') }}</label>
                                    <select name="sana_marhala_id" id="sana_marhala_id" class="{{ $selectClass }}" required>
                                        <option value="" disabled selected>{{ __('Choose year and study stage') }}</option>
                                        @foreach ($seneen_marahel as $sana_marhala)
                                            <option value="{{ $sana_marhala->SanaMarhalaID }}">{{ $sana_marhala->SanaMarhalaName }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="md:col-span-6">
                                    <label for="person_school" class="{{ $labelClass }}">{{ __('School name') }}</label>
                                    <input type="text" name="person_school" id="person_school" class="{{ $inputClass }}">
                                </div>

                                <div class="md:col-span-6">
                                    <label for="school_grad_year" class="{{ $labelClass }}">{{ __('School graduation year') }}</label>
                                    <select name="school_grad_year" id="school_grad_year" class="{{ $selectClass }}">
                                        <option value="" disabled selected>{{ __('Choose school graduation year') }}</option>
                                        @for ($i = 1970; $i <= date('Y'); $i++)
                                            <option value="{{ $i }}">{{ $i }}</option>
                                        @endfor
                                    </select>
                                </div>

                                <div class="md:col-span-6">
                                    <label for="person_faculty" class="{{ $labelClass }}">{{ __('Faculty name') }}</label>
                                    <select name="person_faculty" id="person_faculty" class="{{ $selectClass }}">
                                        <option value="" disabled selected>{{ __('Choose faculty') }}</option>
                                        @foreach ($faculties as $faculty)
                                            <option value="{{ $faculty->FacultyID }}">{{ $faculty->FacultyName }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="md:col-span-6">
                                    <label for="person_university" class="{{ $labelClass }}">{{ __('University name') }}</label>
                                    <select name="person_university" id="person_university" class="{{ $selectClass }}">
                                        <option value="" disabled selected>{{ __('Choose university') }}</option>
                                        @foreach ($universities as $university)
                                            <option value="{{ $university->UniversityID }}">{{ $university->UniversityName }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="md:col-span-6">
                                    <label for="university_grad_year" class="{{ $labelClass }}">{{ __('University graduation year') }}</label>
                                    <select name="university_grad_year" id="university_grad_year" class="{{ $selectClass }}">
                                        <option value="" disabled selected>{{ __('Choose university graduation year') }}</option>
                                        @for ($i = 1970; $i <= date('Y'); $i++)
                                            <option value="{{ $i }}">{{ $i }}</option>
                                        @endfor
                                    </select>
                                </div>

                                <div class="md:col-span-6">
                                    <label for="person_job" class="{{ $labelClass }}">{{ __('Job name') }}</label>
                                    <input type="text" name="person_job" id="person_job" class="{{ $inputClass }}">
                                </div>

                                <div class="md:col-span-6">
                                    <label for="person_job_place" class="{{ $labelClass }}">{{ __('Workplace') }}</label>
                                    <input type="text" name="person_job_place" id="person_job_place" class="{{ $inputClass }}">
                                </div>

                                <div class="md:col-span-6">
                                    <label for="spiritual_father" class="{{ $labelClass }}">{{ __('Spiritual father name') }}</label>
                                    <input type="text" name="spiritual_father" id="spiritual_father" class="{{ $inputClass }}">
                                </div>

                                <div class="md:col-span-12">
                                    <label for="spiritual_father_church" class="{{ $labelClass }}">{{ __('Spiritual father\'s church / confession father\'s church') }}</label>
                                    <input type="text" name="spiritual_father_church" id="spiritual_father_church" class="{{ $inputClass }}">
                                </div>
                            </div>
                        </section>

                        <section class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-5 md:p-6">
                            <div class="flex items-start justify-between gap-4 mb-5">
                                <div>
                                    <h2 class="text-xl font-bold text-slate-900 dark:text-slate-100">{{ __('Part 4: Scout information') }}</h2>
                                    <p class="text-slate-500 dark:text-slate-400 mt-1 text-sm">{{ __('Select scout rank, progress card permit, and sector.') }}</p>
                                </div>
                                <span class="shrink-0 inline-flex items-center rounded-full bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 px-4 py-2 text-sm font-semibold">4 / 4</span>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                                <div class="md:col-span-4">
                                    <label for="rotba_kashfeyya_id" class="{{ $labelClass }}">{{ __('Scout rank') }}</label>
                                    <select name="rotba_kashfeyya_id" id="rotba_kashfeyya_id" class="{{ $selectClass }}">
                                        <option value="" disabled selected>{{ __('Choose scout rank') }}</option>
                                        @foreach ($rotab as $rotba)
                                            <option value="{{ $rotba->RotbaID }}">{{ $rotba->RotbaName }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="md:col-span-4">
                                    <label for="betaka_id" class="{{ $labelClass }}">{{ __('Progress card permit') }}</label>
                                    <select name="betaka_id" id="betaka_id" class="{{ $selectClass }}">
                                        <option value="" disabled selected>{{ __('Choose progress card permit') }}</option>
                                        @foreach ($betakat as $betaka)
                                            <option value="{{ $betaka->EgazetBetakatTaqaddomID }}">{{ $betaka->EgazetBetakatTaqaddomName }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="md:col-span-4">
                                    <label for="qetaa_id" class="{{ $labelClass }}">{{ __('Scout sector') }} **</label>
                                    <select name="qetaa_id" id="qetaa_id" class="{{ $selectClass }}" required>
                                        <option value="" disabled selected>{{ __('Choose sector') }}</option>
                                        @foreach ($qetaat as $qetaa)
                                            <option value="{{ $qetaa->QetaaID }}">{{ $qetaa->QetaaName }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="mt-6 rounded-2xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 p-5 text-amber-900 dark:text-amber-100 text-sm">
                                {{ __('Verify data before continuing') }}
                            </div>
                        </section>

                        <div class="flex flex-col sm:flex-row items-center justify-center gap-4 mt-8">
                            <button type="submit" id="submit-button"
                                class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-8 rounded-xl shadow-lg transition duration-200">
                                {{ __('Continue') }}
                            </button>
                            <a href="{{ url('/index') }}"
                                class="text-sm font-semibold text-slate-600 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition">
                                {{ __('Back to main dashboard') }}
                            </a>
                        </div>

                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection
