<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>كشافة الشمندورة - اظهار بيانات</title>

    <!-- Custom fonts for this template-->
    <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@200;300;500&display=swap');
    </style>
    <!-- Custom styles for this template-->
    <link href="../css/sb-admin-2.min.css" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href={{ asset('img/shamandora.png') }}>
    <link rel="stylesheet" type="text/css"
        href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.0.0-alpha1/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/js/select2.min.js"></script>
</head>

<body class="bg-gradient-primary">
    @if (session('status'))
        <div class="alert alert-success">
            {{ session('status') }}
        </div>
    @endif
    <div class="container mt-4">
        <div class="container">
            <div class="card o-hidden border-0 shadow-lg my-5">
                <div class="card-body p-0">
                    <!-- Nested Row within Card Body -->
                    <div class="col">
                        <div class="col-lg-12">
                            <div class="p-5">
                                <div class="text-center">
                                    <h1 class="h4 text-gray-900 mb-4" style="font-family: 'Cairo', sans-serif;"> ادخال
                                        بيانات ملتحق جديد</h1>
                                    <h2 class="h4 mb-4" style="font-family: 'Cairo', sans-serif; color: brown;"> الجزء
                                        الأول: البيانات الشخصية</h2>
                                </div>
                                <div class="form-group row" dir="rtl">
                                    <div class="col-sm-3">
                                        <label>تسلسل الطلب</label>
                                        <input type="text" class="form-control form-control-user" name="first_name"
                                            id="first_name" style="font-family: 'Cairo', sans-serif; font-size: medium"
                                            value="{{ $person->PersonID }}" disabled>
                                    </div>

                                    {{-- <div class="col-sm-3">
                                        <label>كلمة السر</label>
                                        <input type="text" class="form-control form-control-user" name="second_name"
                                            id="second_name" style="font-family: 'Cairo', sans-serif; font-size: medium"
                                            value="{{ $person->Password }}" disabled>
                                    </div> --}}
                                </div>
                                <br>
                                <div class="form-group row" dir="rtl">
                                    <div class="col-sm-3 mb-3 mb-sm-0">
                                        <label>الاسم الأول</label>
                                        <input type="text" class="form-control form-control-user" name="first_name"
                                            id="first_name" style="font-family: 'Cairo', sans-serif; font-size: medium"
                                            placeholder="الاسم الأول" value="{{ $person->FirstName }}" disabled>
                                    </div>

                                    <div class="col-sm-3">
                                        <label>الاسم الثاني</label>
                                        <input type="text" class="form-control form-control-user" name="second_name"
                                            id="second_name" style="font-family: 'Cairo', sans-serif; font-size: medium"
                                            placeholder="الاسم الثاني" value="{{ $person->SecondName }}" disabled>
                                    </div>

                                    <div class="col-sm-3">
                                        <label>الاسم الثالث</label>
                                        <input type="text" class="form-control form-control-user" name="third_name"
                                            id="third_name" style="font-family: 'Cairo', sans-serif; font-size: medium"
                                            placeholder="الاسم الثالث" value="{{ $person->ThirdName }}" disabled>
                                    </div>

                                    <div class="col-sm-3">
                                        <label>الاسم الرابع</label>
                                        <input type="text" class="form-control form-control-user" name="fourth_name"
                                            id="fourth_name" style="font-family: 'Cairo', sans-serif; font-size: medium"
                                            placeholder="الاسم الرابع" value="{{ $person->FourthName }}" disabled>
                                    </div>
                                </div>
                                <br>
                                <div class="form-group text-center" dir="rtl">
                                    <label for="joindate" style="font-family: 'Cairo', sans-serif;">نوع الملتحق</label>
                                    @if ($person->Gender == 'Male')
                                        <input type="text" class="form-control form-control-user" name="gender"
                                            id="gender" style="font-family: 'Cairo', sans-serif; font-size: medium"
                                            placeholder="النوع" value="ذكر" disabled>
                                    @else
                                        <input type="text" class="form-control form-control-user" name="gender"
                                            id="gender" style="font-family: 'Cairo', sans-serif; font-size: medium"
                                            placeholder="النوع" value="أنثى" disabled>
                                    @endif

                                </div>
                                <br>
                                <div class="form-group text-center" dir="rtl">
                                    <label class="text-center" for="email_input"
                                        style="font-family: 'Cairo', sans-serif;">البريد الالكتروني</label>
                                    <input dir="rtl" type="email" name="email_input" id="email_input"
                                        class="form-control form-control-user"
                                        style="font-family: 'Cairo', sans-serif; font-size: large"
                                        placeholder="أدخل البريد الالكتروني للملتحق بشكل صحيح"
                                        value="{{ $person->PersonalEmail }}" disabled>
                                </div>
                                <br>
                                <div class="form-group row text-center" dir="rtl">
                                    <div class="col-sm-6 mb-3 mb-sm-0">
                                        <label class="text-center" for="birthdate_input"
                                            style="font-family: 'Cairo', sans-serif;">تاريخ الميلاد</label>
                                        <input type="date" class="form-control form-control-user"
                                            id="birthdate_input" name="birthdate_input"
                                            style="margin-left: 5px;;font-family: 'Cairo', sans-serif; font-size: large"
                                            placeholder="تاريخ الميلاد" value="{{ $person->DateOfBirth }}" disabled>
                                    </div>

                                    <div class="col-sm-6 text-center">
                                        <label for="joining_year_input" style="font-family: 'Cairo', sans-serif;">سنة
                                            الالتحاق</label>
                                        <input dir="rtl" type="email" name="email_input" id="email_input"
                                            class="form-control form-control-user"
                                            style="font-family: 'Cairo', sans-serif; font-size: large" placeholder=""
                                            value="{{ $person->ScoutJoiningYear }}" disabled>
                                        </select>

                                    </div>
                                </div>
                                <br>
                                <div class="form-group text-center" dir="rtl">
                                    <label for="joindate" style="font-family: 'Cairo', sans-serif;">الرقم
                                        القومي</label>
                                    <input dir="rtl" type="number" class="form-control form-control-user"
                                        id="input_raqam_qawmy" name="input_raqam_qawmy"
                                        style="font-family: 'Cairo', sans-serif; font-size: large"
                                        placeholder="أدخل الرقم القومي المكون من 14 رقماً"
                                        value="{{ $person->RaqamQawmy }}" disabled>

                                </div>
                                <br>
                                <div class="form-group text-center" dir="rtl">
                                    <label for="facebookLink" style="font-family: 'Cairo', sans-serif;">Facebook
                                        Account URL/Link</label>
                                    @if ($person->FacebookProfileURL == null)
                                        <input dir="rtl" type="text" class="form-control form-control-user"
                                            name="inputFacebookLink" id="inputFacebookLink"
                                            style="font-family: 'Cairo', sans-serif; font-size: large"
                                            placeholder="أدخل لينك حساب الفيسبوك الخاص بالمتلحق (إن وُجِد)"
                                            value="لا يوجد" disabled>
                                    @else
                                        <input dir="rtl" type="text" class="form-control form-control-user"
                                            name="inputFacebookLink" id="inputFacebookLink"
                                            style="font-family: 'Cairo', sans-serif; font-size: large"
                                            placeholder="أدخل لينك حساب الفيسبوك الخاص بالمتلحق (إن وُجِد)"
                                            value="{{ $person->FacebookProfileURL }}" disabled>
                                    @endif
                                </div>
                                <br>
                                <div class="form-group text-center" dir="rtl">
                                    <label for="instagramLink" style="font-family: 'Cairo', sans-serif;">Instagram
                                        Account URL/Link (if Found)</label>
                                    @if ($person->InstagramProfileURL == null)
                                        <input dir="rtl" type="text" class="form-control form-control-user"
                                            name="inputFacebookLink" id="inputFacebookLink"
                                            style="font-family: 'Cairo', sans-serif; font-size: large"
                                            placeholder="أدخل لينك حساب الفيسبوك الخاص بالمتلحق (إن وُجِد)"
                                            value="لا يوجد" disabled>
                                    @else
                                        <input dir="rtl" type="text" class="form-control form-control-user"
                                            name="inputFacebookLink" id="inputFacebookLink"
                                            style="font-family: 'Cairo', sans-serif; font-size: large"
                                            placeholder="أدخل لينك حساب الفيسبوك الخاص بالمتلحق (إن وُجِد)"
                                            value="{{ $person->InstagramProfileURL }}" disabled>
                                    @endif

                                </div>
                                <br>
                                <div class="form-group text-center" dir="rtl">
                                    <label for="joindate" style="font-family: 'Cairo', sans-serif;">فصيلة
                                        الدم</strong></label>
                                    <input dir="rtl" type="text" class="form-control form-control-user"
                                        name="bloodtype" id="bloodtype"
                                        style="font-family: 'Cairo', sans-serif; font-size: large"
                                        value="{{ $person->BloodTypeName }}" disabled>

                                </div>
                                <hr>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-12">
                        <div class="p-5">
                            <div class="text-center">
                                <h1 class="h4 text-gray-900 mb-4" style="font-family: 'Cairo', sans-serif;"> ادخال
                                    بيانات ملتحق جديد</h1>
                                <h2 class="h4 mb-4" style="font-family: 'Cairo', sans-serif; color: brown;"> الجزء
                                    الثاني: بيانات التواصل</h2>
                            </div>
                            <div class="form-group row" dir="rtl">
                                <div class="col-sm-3 mb-3 mb-sm-0">
                                    <label class="text-center" for="personal_phone_number"
                                        style="font-family: 'Cairo', sans-serif;">رقم موبايل الملتحق (الأساسي)</label>
                                    <input type="number" class="form-control form-control-user"
                                        name="personal_phone_number" id="personal_phone_number"
                                        style="font-family: 'Cairo', sans-serif; font-size: medium"
                                        placeholder="رقم الموبايل الشخصي"
                                        value="{{ $person->PersonPersonalMobileNumber }}" disabled>
                                </div>

                                <div class="col-sm-3" dir="rtl">
                                    <label class="text-center" for="father_phone_number"
                                        style="font-family: 'Cairo', sans-serif;">رقم موبايل الأب (إن وُجِد)</label>
                                    <input type="number" class="form-control form-control-user"
                                        name="father_phone_number" id="father_phone_number"
                                        style="font-family: 'Cairo', sans-serif; font-size: medium"
                                        placeholder="رقم موبايل الأب" value="{{ $person->FatherMobileNumber }}"
                                        disabled>
                                </div>

                                <div class="col-sm-3" dir="rtl">
                                    <label class="text-center" for="mother_phone_number"
                                        style="font-family: 'Cairo', sans-serif;">رقم موبايل الأم (إن وُجِد)</label>
                                    <input type="text" class="form-control form-control-user"
                                        name="mother_phone_number" id="mother_phone_number"
                                        style="font-family: 'Cairo', sans-serif; font-size: medium"
                                        placeholder="رقم موبايل الأم" value="{{ $person->MotherMobileNumber }}"
                                        disabled>
                                </div>

                                <div class="col-sm-3" dir="rtl">
                                    <label class="text-center" for="home_phone_number"
                                        style="font-family: 'Cairo', sans-serif;">رقم التليفون الأرضي (إن
                                        وُجِد)</label>
                                    <input type="text" class="form-control form-control-user"
                                        name="home_phone_number" id="home_phone_number"
                                        style="font-family: 'Cairo', sans-serif; font-size: medium"
                                        placeholder="رقم التليفون الأرضي" value="{{ $person->HomePhoneNumber }}"
                                        disabled>
                                </div>
                            </div>
                            <br>
                            <div class="form-group row" dir="rtl">
                                <label for="has_whatsapp"
                                    style="font-family: 'Cairo', sans-serif; margin-top: 5px;">هل رقم الموبايل الأساسي
                                    للملتحق عليه برنامج Whatsapp<strong>(نعم أم لا)</strong></label>
                                <br />
                                <input type="text" class="form-control form-control-user" name="has_whatsapp"
                                    id="has_whatsapp" style="font-family: 'Cairo', sans-serif; font-size: medium"
                                    placeholder="رقم التليفون الأرضي"
                                    value="{{ $person->IsOPersonalPhoneNumberHavingWhatsapp == true ? 'نعم' : 'لا' }}"
                                    disabled>

                            </div>
                            <br />
                            <hr>
                            <div class="form-group row" dir="rtl">
                                <div class="col-sm-4 mb-3 mb-sm-0" dir="rtl">
                                    <label class="text-center" for="building_number"
                                        style="font-family: 'Cairo', sans-serif;">رقم العمارة</label>
                                    <input type="number" class="form-control form-control-user"
                                        name="building_number" id="building_number"
                                        style="font-family: 'Cairo', sans-serif; font-size: medium"
                                        placeholder="أدخل رقم العمارة" value="{{ $person->BuildingNumber }}"
                                        disabled>
                                </div>

                                <div class="col-sm-4 mb-3 mb-sm-0" dir="rtl">
                                    <label class="text-center" for="floor_number"
                                        style="font-family: 'Cairo', sans-serif;">رقم الدور</label>
                                    <input type="number" class="form-control form-control-user" name="floor_number"
                                        id="floor_number" style="font-family: 'Cairo', sans-serif; font-size: medium"
                                        placeholder="أدخل رقم الدور" value="{{ $person->FloorNumber }}" disabled>
                                </div>

                                <div class="col-sm-4 mb-3 mb-sm-0" dir="rtl">
                                    <label class="text-center" for="appartment_number"
                                        style="font-family: 'Cairo', sans-serif;">رقم الشقة</label>
                                    <input type="number" class="form-control form-control-user"
                                        name="appartment_number" id="appartment_number"
                                        style="font-family: 'Cairo', sans-serif; font-size: medium"
                                        placeholder="أدخل رقم الشقة" value="{{ $person->AppartmentNumber }}"
                                        disabled>
                                </div>

                            </div>
                            <br>
                            <div class="form-group row" dir="rtl">
                                <div class="col-sm-6 mb-5 mb-sm-0">
                                    <label class="text-center" for="sub_street_name"
                                        style="font-family: 'Cairo', sans-serif;">اسم الشارع</label>
                                    <input type="text" class="form-control form-control-user"
                                        name="sub_street_name" id="sub_street_name"
                                        style="font-family: 'Cairo', sans-serif; font-size: medium"
                                        placeholder="أدخل اسم الشارع" value="{{ $person->SubStreetName }}" disabled>
                                </div>

                                <div class="col-sm-6 mb-5 mb-sm-0" dir="rtl">
                                    <label class="text-center" for="main_street_name"
                                        style="font-family: 'Cairo', sans-serif;">اسم أقرب شارع رئيسي</label>
                                    <input type="text" class="livesearch form-control form-control-user"
                                        name="main_street_name" id="main_street_name"
                                        style="font-family: 'Cairo', sans-serif; font-size: medium"
                                        placeholder="أدخل اسم أقرب شارع رئيسي للمنزل"
                                        value="{{ $person->MainStreetName }}" disabled>
                                </div>
                            </div>
                            <br>
                            <div class="form-group text-center" dir="rtl">
                                <label class="text-center" for="nearest_landmark"
                                    style="font-family: 'Cairo', sans-serif;">أقرب علامة مميزة</label>
                                <input dir="rtl" type="text" name="nearest_landmark" id="nearest_landmark"
                                    class="form-control form-control-user" id="nearest_landmark"
                                    style="font-family: 'Cairo', sans-serif; font-size: large"
                                    placeholder="أدخل أقرب علامة مميزة لعنوان الملتحق"
                                    value="{{ $person->NearestLandmark }}" disabled>
                            </div>
                            <br>
                            <div class="form-group text-center" dir="rtl">
                                <div class="col-sm-6" dir="rtl">
                                    <label for="manteqa_id" style="font-family: 'Cairo', sans-serif;">المنطقة</label>
                                    <input dir="rtl" type="text" name="manteqa" id="manteqa"
                                        class="form-control form-control-user"
                                        style="font-family: 'Cairo', sans-serif; font-size: large" placeholder=""
                                        value="{{ $person->ManteqaName }}" disabled>
                                </div>
                                <div class="col-sm-6" dir="rtl">
                                    <label for="district_id" style="font-family: 'Cairo', sans-serif;">الحي</label>
                                    <input dir="rtl" type="text" name="district" id="district"
                                        class="form-control form-control-user"
                                        style="font-family: 'Cairo', sans-serif; font-size: large" placeholder=""
                                        value="{{ $person->DistrictName }}" disabled>
                                </div>
                            </div>
                            <hr>
                        </div>
                    </div>

                    <div class="col-lg-12">
                        <div class="p-5">
                            <div class="text-center">
                                <h1 class="h4 text-gray-900 mb-4" style="font-family: 'Cairo', sans-serif;"> ادخال
                                    بيانات ملتحق جديد</h1>
                                <h2 class="h4 mb-4" style="font-family: 'Cairo', sans-serif; color: brown;"> الجزء
                                    الثالث: البيانات الدراسية والكنسية</h2>
                            </div>
                            <div class="form-group text-center" dir="rtl">
                                <label for="sana_marhala_id" style="font-family: 'Cairo', sans-serif;">السنة والمرحلة
                                    الدراسية</label>
                                <input dir="rtl" type="text" name="sana_marhala" id="sana_marhala"
                                    class="form-control form-control-user"
                                    style="font-family: 'Cairo', sans-serif; font-size: large"
                                    value="{{ $person->SanaMarhalaName }}" disabled>
                            </div>

                            </br>
                            <div class="form-group text-center" dir="rtl">
                                <label class="text-center" for="school_name"
                                    style="font-family: 'Cairo', sans-serif;">اسم المدرسة</label>
                                @if ($person->SchoolName == null)
                                    <input dir="rtl" type="text" name="school_name" id="school_name"
                                        class="form-control form-control-user"
                                        style="font-family: 'Cairo', sans-serif; font-size: large" value="لا يوجد"
                                        disabled>
                                @else
                                    <input dir="rtl" type="text" name="school_name" id="school_name"
                                        class="form-control form-control-user"
                                        style="font-family: 'Cairo', sans-serif; font-size: large"
                                        value="{{ $person->SchoolName }}" disabled>
                                @endif
                            </div>
                            </br>
                            <div class="form-group text-center" dir="rtl">
                                <label class="text-center" for="school_grad_year"
                                    style="font-family: 'Cairo', sans-serif;">سنة التخرج من المدرسة</label>
                                @if ($person->SchoolGraduationYear == null)
                                    <input dir="rtl" type="text" name="school_grad_year"
                                        id="school_grad_year" class="form-control form-control-user"
                                        style="font-family: 'Cairo', sans-serif; font-size: large" value="لا يوجد"
                                        disabled>
                                @else
                                    <input dir="rtl" type="text" name="school_grad_year"
                                        id="school_grad_year" class="form-control form-control-user"
                                        style="font-family: 'Cairo', sans-serif; font-size: large"
                                        value="{{ $person->SchoolGraduationYear }}" disabled>
                                @endif
                            </div>
                            </br>
                            <hr>
                            <div class="form-group text-center" dir="rtl">
                                <label class="text-center" for="v"
                                    style="font-family: 'Cairo', sans-serif;">اسم الأب الروحي</label>
                                @if ($person->SpiritualFatherName == null)
                                    <input dir="rtl" type="text" name="spiritual_father"
                                        id="spiritual_father" class="form-control form-control-user"
                                        style="font-family: 'Cairo', sans-serif; font-size: large" value="لا يوجد"
                                        disabled>
                                @else
                                    <input dir="rtl" type="text" name="spiritual_father"
                                        id="spiritual_father" class="form-control form-control-user"
                                        style="font-family: 'Cairo', sans-serif; font-size: large"
                                        value="{{ $person->SpiritualFatherName }}" disabled>
                                @endif
                            </div>
                            <div class="form-group text-center" dir="rtl">
                                <label class="text-center" for="spiritual_father_church"
                                    style="font-family: 'Cairo', sans-serif;">كنيسة الأب الروحي / أب الاعتراف</label>
                                @if ($person->SpiritualFatherChurchName == null)
                                    <input dir="rtl" type="text" name="spiritual_father_church"
                                        id="spiritual_father_church" class="form-control form-control-user"
                                        style="font-family: 'Cairo', sans-serif; font-size: large" value="لا يوجد"
                                        disabled>
                                @else
                                    <input dir="rtl" type="text" name="spiritual_father_church"
                                        id="spiritual_father_church" class="form-control form-control-user"
                                        style="font-family: 'Cairo', sans-serif; font-size: large"
                                        value="{{ $person->SpiritualFatherChurchName }}" disabled>
                                @endif
                            </div>

                        </div>
                    </div>

                    <div class="col-lg-12">
                        <div class="p-5">
                            <div class="text-center">
                                <h1 class="h4 text-gray-900 mb-4" style="font-family: 'Cairo', sans-serif;"> ادخال
                                    بيانات ملتحق جديد</h1>
                                <h2 class="h4 mb-4" style="font-family: 'Cairo', sans-serif; color: brown;"> الجزء
                                    الرابع: البيانات الكشفية</h2>
                            </div>
                            <div class="form-group text-center" dir="rtl">
                                <label style="font-family: 'Cairo', sans-serif;">القطاع الكشفي</label>
                                <input dir="rtl" type="text" name="spiritual_father_church"
                                    id="spiritual_father_church" class="form-control form-control-user"
                                    style="font-family: 'Cairo', sans-serif; font-size: large" placeholder=""
                                    value="{{ $person->QetaaName }}" disabled>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-12">
                        <div class="p-5">
                            <div class="text-center">
                                <h1 class="h4 text-gray-900 mb-4" style="font-family: 'Cairo', sans-serif;"> بيانات
                                    ملتحق</h1>
                                <h2 class="h4 mb-4" style="font-family: 'Cairo', sans-serif; color: brown;"> الجزء
                                    الأخير: الأسئلة المختصة بالقطاع</h2>
                                <h2>{{ $person->QetaaName }}</h2>
                            </div>
                            @if (!$questions->isEmpty())
                                @foreach ($questions as $question)
                                    <div class="form-group" dir="rtl">
                                        <label style="font-family: 'Cairo', sans-serif;">السؤال:
                                            {{ $question->QuestionText }}</label>
                                        </br>
                                        <label style="font-family: 'Cairo', sans-serif;"><strong>إجابة
                                                الملتحق</strong></label>
                                        <input dir="rtl" type="text" name="q" id="q"
                                            class="form-control form-control-user"
                                            style="font-family: 'Cairo', sans-serif; font-size: large" placeholder=""
                                            value="{{ $question->Answer }}" disabled>
                                    </div>
                                @endforeach
                            @else
                                <div class="form-group" dir="rtl">
                                    <label style="font-family: 'Cairo', sans-serif;">لا يوجد أسئلة لهذا الشخص في هذا
                                        القطاع</label>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Bootstrap core JavaScript-->
        <script src="../vendor/jquery/jquery.min.js"></script>
        <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

        <!-- Core plugin JavaScript-->
        <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>

        <!-- Custom scripts for all pages-->
        <script src="../js/sb-admin-2.min.js"></script>

        <!-- Parsley Javascript Validation Form Library -->
        <script src="jquery.js"></script>
        <script src="parsley.min.js"></script>

</body>

</html>
