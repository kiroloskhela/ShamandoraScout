@extends('layouts.app', ['pageTitle' => 'تعديل الملف الشخصي'])

@section('title', 'تعديل الملف | الشمندورة')

@section('content')
@php
    $p = $person;
    $fullName = trim(collect([$p->FirstName ?? '', $p->SecondName ?? '', $p->ThirdName ?? '', $p->FourthName ?? ''])->filter()->implode(' ')) ?: 'المستخدم';
    $code = $p->ShamandoraCode ?? null;
    $photoPath = $p->PersonalImagePath ?? null;
    $photoUrl = null;
    if ($photoPath) {
        $photoUrl = preg_match('#^https?://#i', $photoPath)
            ? $photoPath
            : asset('storage/' . ltrim(preg_replace('#^storage/#', '', $photoPath), '/'));
    }
    $initials = strtoupper(mb_substr($p->FirstName ?? 'م', 0, 1) . mb_substr($p->SecondName ?? '', 0, 1));
    $input = 'w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-slate-900 shadow-sm focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-500/20 transition';
    $readonly = 'w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-slate-600 cursor-not-allowed';
    $label = 'block text-sm font-bold text-slate-700 mb-1.5';
@endphp

<style>
    .profile-edit .hero-banner {
        background: linear-gradient(135deg, #0b5f59 0%, #0f766e 45%, #14b8a6 100%);
    }
</style>

<div class="profile-edit -mx-2 sm:mx-0" dir="rtl">
    @if ($errors->any())
        <div class="mb-4 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-rose-800 text-sm">
            <div class="font-bold mb-1">تحقق من الحقول التالية:</div>
            <ul class="list-disc pr-5 space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section class="overflow-hidden rounded-3xl bg-white shadow-sm ring-1 ring-slate-200/80 mb-6">
        <div class="hero-banner h-24"></div>
        <div class="relative px-5 sm:px-8 pb-5 -mt-12 flex flex-col sm:flex-row sm:items-end gap-4">
            <div class="relative shrink-0">
                @if ($photoUrl)
                    <img id="avatarPreview" src="{{ $photoUrl }}" alt=""
                        class="h-28 w-24 rounded-2xl object-cover ring-4 ring-white shadow-lg bg-slate-100">
                @else
                    <div id="avatarFallback"
                        class="h-28 w-24 rounded-2xl ring-4 ring-white shadow-lg flex items-center justify-center text-2xl font-bold text-white"
                        style="background: linear-gradient(145deg, #0f766e, #134e4a);">
                        {{ $initials }}
                    </div>
                    <img id="avatarPreview" src="" alt="" class="hidden h-28 w-24 rounded-2xl object-cover ring-4 ring-white shadow-lg">
                @endif
            </div>
            <div class="flex-1 min-w-0 pb-1">
                <h1 class="text-xl sm:text-2xl font-bold text-slate-900">تعديل الملف الشخصي</h1>
                <p class="text-sm text-slate-500 mt-1">يمكنك تعديل كل البيانات ما عدا كود الشمندورة والرقم القومي.</p>
            </div>
            <a href="{{ route('profile.show') }}"
                class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-bold px-4 py-2.5 text-sm transition">
                رجوع
            </a>
        </div>
    </section>

    <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-5">
        @csrf
        @method('PUT')

        {{-- Identity (locked) + photos --}}
        <article class="rounded-2xl bg-white p-5 sm:p-6 shadow-sm ring-1 ring-slate-200/80">
            <h2 class="text-lg font-bold text-slate-900 mb-4">الهوية والصور</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="{{ $label }}">كود الشمندورة</label>
                    <input type="text" value="{{ $code ?? '—' }}" class="{{ $readonly }}" readonly dir="ltr">
                </div>
                <div>
                    <label class="{{ $label }}">الرقم القومي</label>
                    <input type="text" value="{{ $p->RaqamQawmy ?? '—' }}" class="{{ $readonly }}" readonly dir="ltr">
                </div>
                <div>
                    <label class="{{ $label }}" for="personal_image">الصورة الشخصية</label>
                    <input type="file" name="personal_image" id="personal_image" accept="image/*"
                        class="{{ $input }} file:ml-3 file:rounded-lg file:border-0 file:bg-teal-50 file:px-3 file:py-1.5 file:text-sm file:font-bold file:text-teal-800">
                </div>
                <div>
                    <label class="{{ $label }}" for="scout_image">الصورة الكشفية</label>
                    <input type="file" name="scout_image" id="scout_image" accept="image/*"
                        class="{{ $input }} file:ml-3 file:rounded-lg file:border-0 file:bg-teal-50 file:px-3 file:py-1.5 file:text-sm file:font-bold file:text-teal-800">
                </div>
            </div>
        </article>

        {{-- Names & basics --}}
        <article class="rounded-2xl bg-white p-5 sm:p-6 shadow-sm ring-1 ring-slate-200/80">
            <h2 class="text-lg font-bold text-slate-900 mb-4">البيانات الأساسية</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="{{ $label }}" for="first_name">الاسم الأول</label>
                    <input class="{{ $input }}" id="first_name" name="first_name" required
                        value="{{ old('first_name', $p->FirstName ?? '') }}">
                </div>
                <div>
                    <label class="{{ $label }}" for="second_name">الاسم الثاني</label>
                    <input class="{{ $input }}" id="second_name" name="second_name" required
                        value="{{ old('second_name', $p->SecondName ?? '') }}">
                </div>
                <div>
                    <label class="{{ $label }}" for="third_name">الاسم الثالث</label>
                    <input class="{{ $input }}" id="third_name" name="third_name"
                        value="{{ old('third_name', $p->ThirdName ?? '') }}">
                </div>
                <div>
                    <label class="{{ $label }}" for="fourth_name">الاسم الرابع</label>
                    <input class="{{ $input }}" id="fourth_name" name="fourth_name"
                        value="{{ old('fourth_name', $p->FourthName ?? '') }}">
                </div>
                <div>
                    <label class="{{ $label }}" for="gender">النوع</label>
                    <select class="{{ $input }}" id="gender" name="gender">
                        <option value="">—</option>
                        <option value="Male" @selected(old('gender', $p->Gender) === 'Male')>ذكر</option>
                        <option value="Female" @selected(old('gender', $p->Gender) === 'Female')>أنثى</option>
                    </select>
                </div>
                <div>
                    <label class="{{ $label }}" for="birthdate_input">تاريخ الميلاد</label>
                    <input type="date" class="{{ $input }}" id="birthdate_input" name="birthdate_input"
                        value="{{ old('birthdate_input', $p->DateOfBirth ? \Illuminate\Support\Str::of($p->DateOfBirth)->substr(0, 10) : '') }}">
                </div>
                <div>
                    <label class="{{ $label }}" for="joining_year_input">سنة الالتحاق</label>
                    <input type="number" class="{{ $input }}" id="joining_year_input" name="joining_year_input"
                        value="{{ old('joining_year_input', $p->ScoutJoiningYear ?? '') }}">
                </div>
                <div>
                    <label class="{{ $label }}" for="blood_type_input">فصيلة الدم</label>
                    <select class="{{ $input }}" id="blood_type_input" name="blood_type_input">
                        <option value="">—</option>
                        @foreach ($blood as $b)
                            <option value="{{ $b->BloodTypeID }}"
                                @selected((string) old('blood_type_input', $p->BloodTypeID) === (string) $b->BloodTypeID)>
                                {{ $b->BloodTypeName }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="sm:col-span-2">
                    <label class="{{ $label }}" for="email_input">البريد الإلكتروني</label>
                    <input type="email" class="{{ $input }}" id="email_input" name="email_input" dir="ltr"
                        value="{{ old('email_input', $p->PersonalEmail ?? '') }}">
                </div>
                <div>
                    <label class="{{ $label }}" for="inputFacebookLink">فيسبوك</label>
                    <input class="{{ $input }}" id="inputFacebookLink" name="inputFacebookLink" dir="ltr"
                        value="{{ old('inputFacebookLink', $p->FacebookProfileURL ?? '') }}">
                </div>
                <div>
                    <label class="{{ $label }}" for="inputInstagramLink">انستجرام</label>
                    <input class="{{ $input }}" id="inputInstagramLink" name="inputInstagramLink" dir="ltr"
                        value="{{ old('inputInstagramLink', $p->InstagramProfileURL ?? '') }}">
                </div>
            </div>
        </article>

        {{-- Contact --}}
        <article class="rounded-2xl bg-white p-5 sm:p-6 shadow-sm ring-1 ring-slate-200/80">
            <h2 class="text-lg font-bold text-slate-900 mb-4">معلومات الاتصال</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <div>
                    <label class="{{ $label }}" for="personal_phone_number">الموبايل الشخصي</label>
                    <input class="{{ $input }}" id="personal_phone_number" name="personal_phone_number" dir="ltr"
                        value="{{ old('personal_phone_number', $p->PersonPersonalMobileNumber ?? '') }}">
                </div>
                <div>
                    <label class="{{ $label }}" for="father_phone_number">موبايل الأب</label>
                    <input class="{{ $input }}" id="father_phone_number" name="father_phone_number" dir="ltr"
                        value="{{ old('father_phone_number', $p->FatherMobileNumber ?? '') }}">
                </div>
                <div>
                    <label class="{{ $label }}" for="mother_phone_number">موبايل الأم</label>
                    <input class="{{ $input }}" id="mother_phone_number" name="mother_phone_number" dir="ltr"
                        value="{{ old('mother_phone_number', $p->MotherMobileNumber ?? '') }}">
                </div>
                <div>
                    <label class="{{ $label }}" for="home_phone_number">التليفون الأرضي</label>
                    <input class="{{ $input }}" id="home_phone_number" name="home_phone_number" dir="ltr"
                        value="{{ old('home_phone_number', $p->HomePhoneNumber ?? '') }}">
                </div>
                <div>
                    <label class="{{ $label }}" for="has_whatsapp">واتساب على الرقم الأساسي</label>
                    <select class="{{ $input }}" id="has_whatsapp" name="has_whatsapp">
                        <option value="1" @selected((string) old('has_whatsapp', $p->IsOPersonalPhoneNumberHavingWhatsapp ?? '0') === '1')>نعم</option>
                        <option value="0" @selected((string) old('has_whatsapp', $p->IsOPersonalPhoneNumberHavingWhatsapp ?? '0') === '0')>لا</option>
                    </select>
                </div>
            </div>
        </article>

        {{-- Address --}}
        <article class="rounded-2xl bg-white p-5 sm:p-6 shadow-sm ring-1 ring-slate-200/80">
            <h2 class="text-lg font-bold text-slate-900 mb-4">العنوان</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <div>
                    <label class="{{ $label }}" for="building_number">رقم المبنى</label>
                    <input class="{{ $input }}" id="building_number" name="building_number"
                        value="{{ old('building_number', $p->BuildingNumber ?? '') }}">
                </div>
                <div>
                    <label class="{{ $label }}" for="floor_number">رقم الدور</label>
                    <input class="{{ $input }}" id="floor_number" name="floor_number"
                        value="{{ old('floor_number', $p->FloorNumber ?? '') }}">
                </div>
                <div>
                    <label class="{{ $label }}" for="appartment_number">رقم الشقة</label>
                    <input class="{{ $input }}" id="appartment_number" name="appartment_number"
                        value="{{ old('appartment_number', $p->AppartmentNumber ?? '') }}">
                </div>
                <div>
                    <label class="{{ $label }}" for="main_street_name">الشارع الرئيسي</label>
                    <input class="{{ $input }}" id="main_street_name" name="main_street_name"
                        value="{{ old('main_street_name', $p->MainStreetName ?? '') }}">
                </div>
                <div>
                    <label class="{{ $label }}" for="sub_street_name">الشارع الفرعي</label>
                    <input class="{{ $input }}" id="sub_street_name" name="sub_street_name"
                        value="{{ old('sub_street_name', $p->SubStreetName ?? '') }}">
                </div>
                <div>
                    <label class="{{ $label }}" for="nearest_landmark">أقرب علامة مميزة</label>
                    <input class="{{ $input }}" id="nearest_landmark" name="nearest_landmark"
                        value="{{ old('nearest_landmark', $p->NearestLandmark ?? '') }}">
                </div>
                <div>
                    <label class="{{ $label }}" for="manteqa_id">المنطقة</label>
                    <select class="{{ $input }}" id="manteqa_id" name="manteqa_id">
                        <option value="">—</option>
                        @foreach ($manateq as $m)
                            <option value="{{ $m->ManteqaID }}"
                                @selected((string) old('manteqa_id', $p->ManteqaID) === (string) $m->ManteqaID)>
                                {{ $m->ManteqaName }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="{{ $label }}" for="district_id">الحي</label>
                    <select class="{{ $input }}" id="district_id" name="district_id">
                        <option value="">—</option>
                        @foreach ($districts as $d)
                            <option value="{{ $d->DistrictID }}"
                                @selected((string) old('district_id', $p->DistrictID) === (string) $d->DistrictID)>
                                {{ $d->DistrictName }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </article>

        {{-- Study / work / scout --}}
        <article class="rounded-2xl bg-white p-5 sm:p-6 shadow-sm ring-1 ring-slate-200/80">
            <h2 class="text-lg font-bold text-slate-900 mb-4">الدراسة والعمل والكشافة</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <div>
                    <label class="{{ $label }}" for="sana_marhala_id">السنة / المرحلة</label>
                    <select class="{{ $input }}" id="sana_marhala_id" name="sana_marhala_id">
                        <option value="">—</option>
                        @foreach ($seneen_marahel as $sm)
                            <option value="{{ $sm->SanaMarhalaID }}"
                                @selected((string) old('sana_marhala_id', $p->SanaMarhalaID) === (string) $sm->SanaMarhalaID)>
                                {{ $sm->SanaMarhalaName }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="{{ $label }}" for="rotba_kashfeyya_id">الرتبة الكشفية</label>
                    <select class="{{ $input }}" id="rotba_kashfeyya_id" name="rotba_kashfeyya_id">
                        <option value="">—</option>
                        @foreach ($rotab as $r)
                            <option value="{{ $r->RotbaID }}"
                                @selected((string) old('rotba_kashfeyya_id', $p->RotbaID) === (string) $r->RotbaID)>
                                {{ $r->RotbaName }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="{{ $label }}" for="betaka_id">بطاقة التقدم / الوسام</label>
                    <select class="{{ $input }}" id="betaka_id" name="betaka_id">
                        <option value="">—</option>
                        @foreach ($betakat as $bt)
                            <option value="{{ $bt->EgazetBetakatTaqaddomID }}"
                                @selected((string) old('betaka_id', $p->EgazetBetakatTaqaddomID) === (string) $bt->EgazetBetakatTaqaddomID)>
                                {{ $bt->EgazetBetakatTaqaddomName }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="{{ $label }}" for="school_name">المدرسة</label>
                    <input class="{{ $input }}" id="school_name" name="school_name"
                        value="{{ old('school_name', $p->SchoolName ?? '') }}">
                </div>
                <div>
                    <label class="{{ $label }}" for="school_grad_year">سنة تخرج المدرسة</label>
                    <input class="{{ $input }}" id="school_grad_year" name="school_grad_year"
                        value="{{ old('school_grad_year', $p->SchoolGraduationYear ?? '') }}">
                </div>
                <div>
                    <label class="{{ $label }}" for="person_university">الجامعة</label>
                    <select class="{{ $input }}" id="person_university" name="person_university">
                        <option value="">—</option>
                        @foreach ($universities as $u)
                            <option value="{{ $u->UniversityID }}"
                                @selected((string) old('person_university', $p->UniversityID) === (string) $u->UniversityID)>
                                {{ $u->UniversityName }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="{{ $label }}" for="person_faculty">الكلية</label>
                    <select class="{{ $input }}" id="person_faculty" name="person_faculty">
                        <option value="">—</option>
                        @foreach ($faculties as $f)
                            <option value="{{ $f->FacultyID }}"
                                @selected((string) old('person_faculty', $p->FacultyID) === (string) $f->FacultyID)>
                                {{ $f->FacultyName }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="{{ $label }}" for="university_grad_year">سنة تخرج الجامعة</label>
                    <input class="{{ $input }}" id="university_grad_year" name="university_grad_year"
                        value="{{ old('university_grad_year', $p->ActualFacultyGraduationYear ?? '') }}">
                </div>
                <div>
                    <label class="{{ $label }}" for="person_job">الوظيفة</label>
                    <input class="{{ $input }}" id="person_job" name="person_job"
                        value="{{ old('person_job', $p->JobName ?? '') }}">
                </div>
                <div>
                    <label class="{{ $label }}" for="person_job_place">مكان العمل</label>
                    <input class="{{ $input }}" id="person_job_place" name="person_job_place"
                        value="{{ old('person_job_place', $p->WorkPlace ?? '') }}">
                </div>
                <div>
                    <label class="{{ $label }}" for="spiritual_father">أب الاعتراف</label>
                    <input class="{{ $input }}" id="spiritual_father" name="spiritual_father"
                        value="{{ old('spiritual_father', $p->SpiritualFatherName ?? '') }}">
                </div>
                <div>
                    <label class="{{ $label }}" for="spiritual_father_church">كنيسة أب الاعتراف</label>
                    <input class="{{ $input }}" id="spiritual_father_church" name="spiritual_father_church"
                        value="{{ old('spiritual_father_church', $p->SpiritualFatherChurchName ?? '') }}">
                </div>
            </div>
        </article>

        <div class="flex flex-wrap gap-3 justify-start sticky bottom-4 z-10">
            <button type="submit"
                class="inline-flex items-center justify-center rounded-xl bg-teal-700 hover:bg-teal-800 active:bg-teal-900 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 text-white font-bold px-6 py-3 text-sm shadow-lg shadow-teal-900/10 transition">
                حفظ التعديلات
            </button>
            <a href="{{ route('profile.show') }}"
                class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-bold px-6 py-3 text-sm transition">
                إلغاء
            </a>
        </div>
    </form>

    <article class="mt-8 rounded-2xl bg-white p-5 sm:p-6 shadow-sm ring-1 ring-slate-200/80">
        <h2 class="text-lg font-bold text-slate-900 mb-4">تغيير كلمة المرور</h2>
        <form method="POST" action="{{ route('profile.password.update') }}" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            @csrf
            @method('PUT')
            <div>
                <label class="{{ $label }}" for="password">كلمة مرور جديدة</label>
                <input type="password" class="{{ $input }}" name="password" id="password" autocomplete="new-password">
                @error('password')
                    <p class="text-rose-600 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="{{ $label }}" for="password_confirmation">تأكيد كلمة المرور</label>
                <input type="password" class="{{ $input }}" name="password_confirmation" id="password_confirmation" autocomplete="new-password">
            </div>
            <div class="sm:col-span-2">
                <button type="submit"
                    class="inline-flex items-center justify-center rounded-xl bg-slate-800 hover:bg-slate-900 text-white font-bold px-5 py-3 text-sm transition">
                    تحديث كلمة المرور
                </button>
            </div>
        </form>
    </article>
</div>

<script>
    document.getElementById('personal_image')?.addEventListener('change', function (e) {
        const file = e.target.files?.[0];
        if (!file) return;
        const preview = document.getElementById('avatarPreview');
        const fallback = document.getElementById('avatarFallback');
        const url = URL.createObjectURL(file);
        if (preview) {
            preview.src = url;
            preview.classList.remove('hidden');
        }
        if (fallback) fallback.classList.add('hidden');
    });
</script>
@endsection
