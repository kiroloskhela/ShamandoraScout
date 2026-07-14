@extends('layouts.app' , ['pageTitle' => "اضافة شخص جديد"?? ''])


@section('content')
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>كشافة الشمندورة - ادخال بيانات</title>

    <!-- Custom fonts for this template-->
    <link href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">
    <style>
    @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@200;300;500&display=swap');
    </style>
    <!-- Custom styles for this template-->
    <link href="../css/sb-admin-2.min.css" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="{{ asset('img/shamandora.png') }}">
    <link rel="stylesheet" type="text/css"
        href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.0.0-alpha1/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/js/select2.min.js"></script>
</head>

<body class="bg-gradient-primary">
    @if(session('status'))
    <div class="alert alert-success">
        {{ session('status') }}
    </div>
    @endif
    <div class="container mt-4">
        <div class="container">
            <form class="user" id="regForm" method="POST" action="{{ route('person.insert') }}">
                @csrf
                <div class="card o-hidden border-0 shadow-lg my-5">
                    <div class="card-body p-0">
                        <!-- Nested Row within Card Body -->
                        <div class="col">
                            <div class="col-lg-12">
                                <div class="p-5">
                                    <div class="text-center">
                                        <h1 class="h4 text-gray-900 mb-4" style="font-family: 'Cairo', sans-serif;">
                                            ادخال بيانات ملتحق جديد</h1>
                                        <h2 class="h4 mb-4" style="font-family: 'Cairo', sans-serif; color: brown;">
                                            الجزء الأول: البيانات الشخصية</h2>
                                    </div>
                                    <div>
                                        <input type="text" class="form-control form-control-user" name="RequestPersonID"
                                            id="RequestPersonID"
                                            style="font-family: 'Cairo', sans-serif; font-size: medium"
                                            value="{{Auth()->user()->PersonID}}" hidden>
                                    </div>
                                    <div class="form-group row" dir="rtl">
                                        <div class="col-sm-3 mb-3 mb-sm-0">
                                            <input type="text" class="form-control form-control-user" name="first_name"
                                                id="first_name"
                                                style="font-family: 'Cairo', sans-serif; font-size: medium"
                                                placeholder="الاسم الأول" onclick="validate('first_name')"
                                                onfocusout="validate('first_name')">
                                        </div>

                                        <div class="col-sm-3">
                                            <input type="text" class="form-control form-control-user" name="second_name"
                                                id="second_name"
                                                style="font-family: 'Cairo', sans-serif; font-size: medium"
                                                placeholder="الاسم الثاني" onclick="validate('second_name')"
                                                onfocusout="validate('second_name')">
                                        </div>

                                        <div class="col-sm-3">
                                            <input type="text" class="form-control form-control-user" name="third_name"
                                                id="third_name"
                                                style="font-family: 'Cairo', sans-serif; font-size: medium"
                                                placeholder="الاسم الثالث" onclick="validate('third_name')"
                                                onfocusout="validate('third_name')">
                                        </div>

                                        <div class="col-sm-3">
                                            <input type="text" class="form-control form-control-user" name="fourth_name"
                                                id="fourth_name"
                                                style="font-family: 'Cairo', sans-serif; font-size: medium"
                                                placeholder="الاسم الرابع">
                                        </div>
                                    </div>
                                    <div class="form-group row text-center" dir="rtl">
                                        <label for="joindate" style="font-family: 'Cairo', sans-serif;">اختر نوع
                                            الملتحق<strong>(ذكر أم أنثى)</strong> **</label>
                                        <br />
                                        <select class="form-control col-sm-4" style="margin-right: 20px;" name="gender"
                                            id="gender" onChange="" placeholder="اختار نوع الملتحق"
                                            onclick="validate('gender')" onfocusout="validate('gender')">
                                            <option
                                                style="font-family: 'Cairo', sans-serif; color: black; font-size: large"
                                                value="" disabled selected>اختر النوع</option>
                                            <option style="font-family: 'Cairo', sans-serif; color: black;"
                                                value="Male">ذكر</option>
                                            <option style="font-family: 'Cairo', sans-serif; color: black;"
                                                value="Female">أنثى</option>
                                        </select>
                                    </div>
                                    <div class="form-group text-center" dir="rtl">
                                        <label class="text-center" for="email_input"
                                            style="font-family: 'Cairo', sans-serif;">البريد الالكتروني</label>
                                        <input dir="rtl" type="email" name="email_input" id="email_input"
                                            class="form-control form-control-user"
                                            style="font-family: 'Cairo', sans-serif; font-size: large"
                                            placeholder="أدخل البريد الالكتروني للملتحق بشكل صحيح">
                                    </div>

                                    <div class="form-group row text-center" dir="rtl">
                                        <div class="col-sm-6 mb-3 mb-sm-0">
                                            <label class="text-center" for="birthdate_input"
                                                style="font-family: 'Cairo', sans-serif;">تاريخ الميلاد **</label>
                                            <input type="date" class="form-control form-control-user"
                                                id="birthdate_input" name="birthdate_input"
                                                style="margin-left: 5px;;font-family: 'Cairo', sans-serif; font-size: large"
                                                placeholder="تاريخ الميلاد" onclick="validate('birthdate_input')"
                                                onfocusout="validate('birthdate_input')">
                                        </div>

                                        <div class="col-sm-6 text-center">
                                            <label for="joining_year_input"
                                                style="font-family: 'Cairo', sans-serif;">سنة الالتحاق **</label>
                                            <br />
                                            <select class="form-control" style="margin-top: 8px;"
                                                name="joining_year_input" id="joining_year_input" onChange=""
                                                placeholder="اختار سنة الالتحاق بالكشافة"
                                                onclick="validate('joining_year_input')"
                                                onfocusout="validate('joining_year_input')">
                                                <option
                                                    style="font-family: 'Cairo', sans-serif; color: black; font-size: large"
                                                    value="" disabled selected>اختر سنة الالتحاق بالكشافة</option>
                                                @for($i = 1990; $i <= date('Y'); $i++) <option
                                                    style="font-family: 'Cairo', sans-serif; color: black;"
                                                    value={{$i}}>{{$i}}</option>
                                                    @endfor
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group text-center" dir="rtl">
                                        <label for="joindate" style="font-family: 'Cairo', sans-serif;">الرقم القومي
                                            **</label>
                                        <input dir="rtl" type="number" class="form-control form-control-user"
                                            id="input_raqam_qawmy" name="input_raqam_qawmy"
                                            style="font-family: 'Cairo', sans-serif; font-size: large"
                                            placeholder="أدخل الرقم القومي المكون من 14 رقماً"
                                            onclick="validate('input_raqam_qawmy')"
                                            onfocusout="validate('input_raqam_qawmy')">

                                    </div>
                                    <div class="form-group text-center" dir="rtl">
                                        <label for="inputFacebookLink"
                                            style="font-family: 'Cairo', sans-serif;">Facebook Account URL/Link (if
                                            Found)</label>
                                        <input dir="rtl" type="text" class="form-control form-control-user"
                                            name="inputFacebookLink" id="inputFacebookLink"
                                            style="font-family: 'Cairo', sans-serif; font-size: large"
                                            placeholder="أدخل لينك حساب الفيسبوك الخاص بالمتلحق (إن وُجِد)">

                                    </div>
                                    <div class="form-group text-center" dir="rtl">
                                        <label for="instagramLink" style="font-family: 'Cairo', sans-serif;">Instagram
                                            Account URL/Link (if Found)</label>
                                        <input dir="rtl" type="text" class="form-control form-control-user"
                                            name="inputInstagramLink" id="inputInstagramLink"
                                            style="font-family: 'Cairo', sans-serif; font-size: large"
                                            placeholder="أدخل لينك حساب انستجرام الخاص بالمتلحق (إن وُجِد)">

                                    </div>
                                    <div class="form-group row text-center" dir="rtl">
                                        <label for="joindate" style="font-family: 'Cairo', sans-serif;">اختر فصيلة الدم
                                            الصحيحة <strong>(اختر "غير محدد" عند عدم التأكد)</strong> **</label>
                                        <br />
                                        <select class="form-control col-sm-4" style="margin-right: 20px;"
                                            name="blood_type_input" id="blood_type_input" onChange=""
                                            placeholder="اختار سنة الالتحاق بالكشافة"
                                            onclick="validate('blood_type_input')"
                                            onfocusout="validate('blood_type_input')">
                                            <option
                                                style="font-family: 'Cairo', sans-serif; color: black; font-size: large"
                                                value="" disabled selected>اختر فصيلة الدم</option>
                                            @foreach($blood as $blood_element)
                                            <option style="font-family: 'Cairo', sans-serif; color: black;"
                                                value="{{$blood_element->BloodTypeID}}">
                                                {{$blood_element->BloodTypeName}}</option>
                                            @endforeach
                                        </select>

                                    </div>

                                    <hr>
                                    <div class="text-center">
                                        <a class="small" style="font-family: 'Cairo', sans-serif;"
                                            href={{ url('/index') }}>الرجوع إلى لوحة التحكم الرئيسية</a>
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
                                <div class="form-group row text-center" dir="rtl">
                                    <div class="col-sm-3 mb-3 mb-sm-0">
                                        <label class="text-center" for="personal_phone_number"
                                            style="font-family: 'Cairo', sans-serif;">رقم موبايل الملتحق (الأساسي)
                                            **</label>
                                        <input type="number" class="form-control form-control-user"
                                            name="personal_phone_number" id="personal_phone_number"
                                            style="font-family: 'Cairo', sans-serif; font-size: medium"
                                            placeholder="رقم الموبايل الشخصي"
                                            onclick="validate('personal_phone_number')"
                                            onfocusout="validate('personal_phone_number')">
                                    </div>

                                    <div class="col-sm-3 text-center" dir="rtl">
                                        <label class="text-center" for="father_phone_number"
                                            style="font-family: 'Cairo', sans-serif;">رقم موبايل الأب (إن وُجِد)</label>
                                        <input type="number" class="form-control form-control-user"
                                            name="father_phone_number" id="father_phone_number"
                                            style="font-family: 'Cairo', sans-serif; font-size: medium"
                                            placeholder="رقم موبايل الأب">
                                    </div>

                                    <div class="col-sm-3 text-center" dir="rtl">
                                        <label class="text-center" for="mother_phone_number"
                                            style="font-family: 'Cairo', sans-serif;">رقم موبايل الأم (إن وُجِد)</label>
                                        <input type="text" class="form-control form-control-user"
                                            name="mother_phone_number" id="mother_phone_number"
                                            style="font-family: 'Cairo', sans-serif; font-size: medium"
                                            placeholder="رقم موبايل الأم">
                                    </div>

                                    <div class="col-sm-3 text-center" dir="rtl">
                                        <label class="text-center" for="home_phone_number"
                                            style="font-family: 'Cairo', sans-serif;">رقم التليفون الأرضي (إن
                                            وُجِد)</label>
                                        <input type="text" class="form-control form-control-user"
                                            name="home_phone_number" id="home_phone_number"
                                            style="font-family: 'Cairo', sans-serif; font-size: medium"
                                            placeholder="رقم التليفون الأرضي">
                                    </div>
                                </div>
                                <div class="form-group row text-center" dir="rtl">
                                    <label for="has_whatsapp"
                                        style="font-family: 'Cairo', sans-serif; margin-top: 5px;">هل رقم الموبايل
                                        الأساسي للملتحق عليه برنامج Whatsapp<strong>(نعم أم لا)</strong></label>
                                    <br />
                                    <select class="form-control col-sm-4" style="margin-right: 20px;"
                                        name="has_whatsapp" id="has_whatsapp" onChange=""
                                        placeholder="هل الموبايل الأساسي عليه واتساب؟" id="has_whatsapp">
                                        <option style="font-family: 'Cairo', sans-serif; color: black; font-size: large"
                                            value="" disabled selected>اختر نعم أم لا</option>
                                        <option style="font-family: 'Cairo', sans-serif; color: black;" value="True">نعم
                                        </option>
                                        <option style="font-family: 'Cairo', sans-serif; color: black;" value="False">لا
                                        </option>
                                    </select>

                                </div>
                                <br />
                                <hr>
                                <div class="form-group row text-center" dir="rtl">
                                    <div class="col-sm-4 mb-3 mb-sm-0" dir="rtl">
                                        <label class="text-center" for="building_number"
                                            style="font-family: 'Cairo', sans-serif;">رقم العمارة **</label>
                                        <input type="number" class="form-control form-control-user"
                                            name="building_number" id="building_number"
                                            style="font-family: 'Cairo', sans-serif; font-size: medium"
                                            placeholder="أدخل رقم العمارة" onclick="validate('building_number')"
                                            onfocusout="validate('building_number')">
                                    </div>

                                    <div class="col-sm-4 mb-3 mb-sm-0 text-center" dir="rtl">
                                        <label class="text-center" for="floor_number"
                                            style="font-family: 'Cairo', sans-serif;">رقم الدور **</label>
                                        <input type="number" class="form-control form-control-user" name="floor_number"
                                            id="floor_number"
                                            style="font-family: 'Cairo', sans-serif; font-size: medium"
                                            placeholder="أدخل رقم الدور">
                                    </div>

                                    <div class="col-sm-4 mb-3 mb-sm-0 text-center" dir="rtl">
                                        <label class="text-center" for="appartment_number"
                                            style="font-family: 'Cairo', sans-serif;">رقم الشقة **</label>
                                        <input type="number" class="form-control form-control-user"
                                            name="appartment_number" id="appartment_number"
                                            style="font-family: 'Cairo', sans-serif; font-size: medium"
                                            placeholder="أدخل رقم الشقة">
                                    </div>

                                </div>
                                <div class="form-group row text-center" dir="rtl">
                                    <div class="col-sm-6 mb-5 mb-sm-0">
                                        <label class="text-center" for="sub_street_name"
                                            style="font-family: 'Cairo', sans-serif;">اسم الشارع **</label>
                                        <input type="text" class="form-control form-control-user" name="sub_street_name"
                                            id="sub_street_name"
                                            style="font-family: 'Cairo', sans-serif; font-size: medium"
                                            placeholder="أدخل اسم الشارع" onclick="validate('sub_street_name')"
                                            onfocusout="validate('sub_street_name')">
                                    </div>

                                    <div class="col-sm-6 mb-5 mb-sm-0 text-center" dir="rtl">
                                        <label class="text-center" for="main_street_name"
                                            style="font-family: 'Cairo', sans-serif;">اسم أقرب شارع رئيسي **</label>
                                        <input type="text" class="livesearch form-control form-control-user"
                                            name="main_street_name" id="main_street_name"
                                            style="font-family: 'Cairo', sans-serif; font-size: medium"
                                            placeholder="أدخل اسم أقرب شارع رئيسي للمنزل">
                                    </div>
                                </div>
                                <div class="form-group text-center" dir="rtl">
                                    <label class="text-center" for="nearest_landmark"
                                        style="font-family: 'Cairo', sans-serif;">أقرب علامة مميزة</label>
                                    <input dir="rtl" type="text" name="nearest_landmark" id="nearest_landmark"
                                        class="form-control form-control-user" id="nearest_landmark"
                                        style="font-family: 'Cairo', sans-serif; font-size: large"
                                        placeholder="أدخل أقرب علامة مميزة لعنوان الملتحق">
                                </div>
                                <div class="form-group text-center" dir="rtl">
                                    <div class="col-sm-6" dir="rtl">
                                        <label for="manteqa_id" style="font-family: 'Cairo', sans-serif;">المنطقة
                                            **</label>
                                        <br />
                                        <select class="form-control" style="margin-top: 8px;" name="manteqa_id"
                                            id="manteqa_id" placeholder="اختار المنطقة" onclick="validate('manteqa_id')"
                                            onfocusout="validate('manteqa_id')">
                                            <option
                                                style="font-family: 'Cairo', sans-serif; color: black; font-size: large"
                                                value="" disabled selected> اختر المنطقة السكنية</option>
                                            @foreach($manateq as $manteqa)
                                            <option style="font-family: 'Cairo', sans-serif; color: black;"
                                                value="{{$manteqa->ManteqaID}}">{{$manteqa->ManteqaName}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-sm-6 text-center" dir="rtl">
                                        <label for="district_id" style="font-family: 'Cairo', sans-serif;">الحي
                                            **</label>
                                        <br />
                                        <select class="form-control" style="margin-top: 8px;" name="district_id"
                                            id="district_id" onChange="" placeholder="اختار الحي"
                                            onclick="validate('district_id')" onfocusout="validate('district_id')">
                                            <option
                                                style="font-family: 'Cairo', sans-serif; color: black; font-size: large"
                                                value="" disabled selected> اختر الحي</option>
                                            @foreach($districts as $district)
                                            <option style="font-family: 'Cairo', sans-serif; color: black;"
                                                value="{{$district->DistrictID}}">{{$district->DistrictName}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <hr>
                                <div class="text-center">
                                    <a class="small" style="font-family: 'Cairo', sans-serif;"
                                        href={{ url('/index') }}>الرجوع إلى لوحة التحكم الرئيسية</a>
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
                                    <label for="sana_marhala_id" style="font-family: 'Cairo', sans-serif;">السنة
                                        والمرحلة الدراسية</label>
                                    <br />
                                    <select class="form-control" style="margin-top: 8px;" name="sana_marhala_id"
                                        id="sana_marhala_id" onselect="checkMarhala()"
                                        placeholder="اختار السنة والمرحلة الدراسية"
                                        onclick="validate('sana_marhala_id')" onfocusout="validate('sana_marhala_id')">
                                        <option style="font-family: 'Cairo', sans-serif; color: black; font-size: large"
                                            value="" disabled selected> اختر السنة والمرحلة الدراسية</option>
                                        @foreach($seneen_marahel as $sana_marhala)
                                        <option style="font-family: 'Cairo', sans-serif; color: black;"
                                            value="{{$sana_marhala->SanaMarhalaID}}">{{$sana_marhala->SanaMarhalaName}}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                <br />

                                <div class="form-group text-center" dir="rtl">
                                    <label for="person_school" style="font-family: 'Cairo', sans-serif;">اسم
                                        المدرسة</label>
                                    <input dir="rtl" type="text" name="person_school" id="person_school"
                                        class="form-control form-control-user"
                                        style="font-family: 'Cairo', sans-serif; font-size: large"
                                        placeholder="أدخل اسم المدرسة">
                                </div>
                                <br />

                                <div class="form-group text-center">
                                    <label for="school_grad_year" style="font-family: 'Cairo', sans-serif;">سنة التخرج
                                        من المدرسة (في حالة الانتهاء من المدرسة)</label>
                                    <br />
                                    <select class="form-control rtl" style="margin-top: 8px; text-align: right;"
                                        name="school_grad_year" id="school_grad_year" onChange=""
                                        placeholder="اختار سنة التخرج من المدرسة">
                                        <option style="font-family: 'Cairo', sans-serif; color: black; font-size: large"
                                            value="" disabled selected>اختر سنة التخرج من المدرسة</option>
                                        @for($i = 1970; $i <= date('Y'); $i++) <option
                                            style="font-family: 'Cairo', sans-serif; color: black;" value={{$i}}>{{$i}}
                                            </option>
                                            @endfor
                                    </select>
                                </div>

                                <div class="form-group text-center" dir="rtl">
                                    <label class="text-center" for="person_faculty"
                                        style="font-family: 'Cairo', sans-serif;">اسم الكلية</label>
                                    <select class="form-control" style="margin-top: 8px;" name="person_faculty"
                                        id="person_faculty" placeholder="اختار الكلية">
                                        <option style="font-family: 'Cairo', sans-serif; color: black; font-size: large"
                                            value="" disabled selected> اختر الكلية</option>
                                        @foreach($faculties as $faculty)
                                        <option style="font-family: 'Cairo', sans-serif; color: black;"
                                            value="{{$faculty->FacultyID}}">{{$faculty->FacultyName}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                </br>
                                <div class="form-group text-center" dir="rtl">
                                    <label class="text-center" for="person_university"
                                        style="font-family: 'Cairo', sans-serif;">اسم الجامعة</label>
                                    <select class="form-control" style="margin-top: 8px;" name="person_university"
                                        id="person_university" placeholder="اختار الجامعة">
                                        <option style="font-family: 'Cairo', sans-serif; color: black; font-size: large"
                                            value="" disabled selected> اختر الجامعة</option>
                                        @foreach($universities as $university)
                                        <option style="font-family: 'Cairo', sans-serif; color: black;"
                                            value="{{$university->UniversityID}}">{{$university->UniversityName}}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                </br>
                                <div class="form-group text-center">
                                    <label for="university_grad_year" style="font-family: 'Cairo', sans-serif;">سنة
                                        التخرج من الجامعة (في حالة الانتهاء من الجامعة)</label>
                                    <br />
                                    <select class="form-control" style="margin-top: 8px; text-align: right;"
                                        name="university_grad_year" id="university_grad_year" onChange=""
                                        placeholder="اختار سنة التخرج من الجامعة">
                                        <option style="font-family: 'Cairo', sans-serif; color: black; font-size: large"
                                            value="" disabled selected>اختر سنة التخرج من الجامعة</option>
                                        @for($i = 1970; $i <= date('Y'); $i++) <option
                                            style="font-family: 'Cairo', sans-serif; color: black;" value={{$i}}>{{$i}}
                                            </option>
                                            @endfor
                                    </select>
                                </div>

                                <div class="form-group text-center" dir="rtl">
                                    <label class="text-center" for="person_job"
                                        style="font-family: 'Cairo', sans-serif;">نوع الوظيفة</label>
                                    <input dir="rtl" type="text" name="person_job" id="person_job"
                                        class="form-control form-control-user"
                                        style="font-family: 'Cairo', sans-serif; font-size: large"
                                        placeholder="أدخل الوظيفة للخريجين">
                                </div>

                                <div class="form-group text-center" dir="rtl">
                                    <label class="text-center" for="person_job_place"
                                        style="font-family: 'Cairo', sans-serif;">مكان العمل الحالي</label>
                                    <input dir="rtl" type="text" name="person_job_place" id="person_job_place"
                                        class="form-control form-control-user"
                                        style="font-family: 'Cairo', sans-serif; font-size: large"
                                        placeholder="أدخل مكان العمل الحالي للخريجين">
                                </div>

                                <hr>
                                <div class="form-group text-center" dir="rtl">
                                    <label class="text-center" for="spiritual_father"
                                        style="font-family: 'Cairo', sans-serif;">الأب الروحي / أب الاعتراف</label>
                                    <input dir="rtl" type="text" name="spiritual_father" id="spiritual_father"
                                        class="form-control form-control-user"
                                        style="font-family: 'Cairo', sans-serif; font-size: large"
                                        placeholder="أدخل اسم أب الاعتراف أو الأب الروحي للملتحق">
                                </div>
                                <div class="form-group text-center" dir="rtl">
                                    <label class="text-center" for="spiritual_father_church"
                                        style="font-family: 'Cairo', sans-serif;">كنيسة الأب الروحي / أب
                                        الاعتراف</label>
                                    <input dir="rtl" type="text" name="spiritual_father_church"
                                        id="spiritual_father_church" class="form-control form-control-user"
                                        style="font-family: 'Cairo', sans-serif; font-size: large"
                                        placeholder="أدخل كنيسة أب الاعتراف أو الأب الروحي للملتحق">
                                </div>

                                <div class="text-center">
                                    <a class="small" style="font-family: 'Cairo', sans-serif;"
                                        href={{ url('/index') }}>الرجوع إلى لوحة التحكم الرئيسية</a>
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
                                    <label style="font-family: 'Cairo', sans-serif;">الرتبة الكشفية</label>
                                    <br />
                                    <select class="form-control" style="margin-top: 8px;" name="rotba_kashfeyya_id"
                                        id="rotba_kashfeyya_id" onChange="" placeholder="اختار الرتبة الكشفية"
                                        onclick="validate('rotba_kashfeyya_id')"
                                        onfocusout="validate('rotba_kashfeyya_id')">
                                        <option style="font-family: 'Cairo', sans-serif; color: black; font-size: large"
                                            value="" disabled selected> اختر الرتبة الكشفية</option>
                                        @foreach($rotab as $rotba)
                                        <option style="font-family: 'Cairo', sans-serif; color: black;"
                                            value="{{$rotba->RotbaID}}">{{$rotba->RotbaName}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group text-center" dir="rtl">
                                    <label style="font-family: 'Cairo', sans-serif;">ايجازة بطاقة التقدم</label>
                                    <br />
                                    <select class="form-control" style="margin-top: 8px;" name="betaka_id"
                                        id="betaka_id" onChange="" placeholder="اختار ايجازة بطاقة التقدم" onclick=""
                                        onfocusout="">
                                        <option style="font-family: 'Cairo', sans-serif; color: black; font-size: large"
                                            value="" disabled selected> اختر ايجازة بطاقة التقدم</option>
                                        @foreach($betakat as $betaka)
                                        <option style="font-family: 'Cairo', sans-serif; color: black;"
                                            value="{{$betaka->EgazetBetakatTaqaddomID}}">
                                            {{$betaka->EgazetBetakatTaqaddomName}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group text-center" dir="rtl">
                                    <label style="font-family: 'Cairo', sans-serif;">القطاع الكشفي</label>
                                    <br />
                                    <select class="form-control" style="margin-top: 8px;" name="qetaa_id" id="qetaa_id"
                                        onChange="" placeholder="اختار القطاع" onclick="validate('qetaa_id')"
                                        onfocusout="validate('qetaa_id')">
                                        <option style="font-family: 'Cairo', sans-serif; color: black; font-size: large"
                                            value="" disabled selected> اختر القطاع</option>
                                        @foreach($qetaat as $qetaa)
                                        <option style="font-family: 'Cairo', sans-serif; color: black;"
                                            value="{{$qetaa->QetaaID}}">{{$qetaa->QetaaName}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <label><strong>برجاء التأكد من البيانات مرة أخرى قبل ضغط "استمرار" علماً بأن سيتم
                                        الانتقال إلى باقي الأسئلة الخاصة بالقطاع بعد الضغط</strong></label>
                                <input type="submit" class="btn-google btn-user btn-block"
                                    style="background-color: brown;" id="submit-button" value="استمرار"
                                    onsubmit=""></input>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

        </div>

        <script>
        function validate(ElementId) {
            const element = document.getElementById(ElementId);
            if (element.value == '') {
                element.style.backgroundColor = '#C53939';
                element.style.color = '#FFFFFF';
                document.getElementById('submit-button').disabled = true;
            } else {
                element.style.backgroundColor = 'White';
                element.style.color = '#1D43EC';
                document.getElementById('submit-button').disabled = false;
            }
        }
        </script>

        <!-- Bootstrap core JavaScript-->
        <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
        <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>

        <!-- Core plugin JavaScript-->
        <script src="{{ asset('vendor/jquery-easing/jquery.easing.min.js') }}"></script>

        <!-- Custom scripts for all pages-->
        <script src="../js/sb-admin-2.min.js"></script>

        <!-- Parsley Javascript Validation Form Library -->
        <script src="jquery.js"></script>
        <script src="parsley.min.js"></script>

</body>

</html>
@endsection