@extends('layouts.app', ['pageTitle' => 'انواع المجموعات الكشفية'])
@section('content')
    <x-form-card title="إضافة نوع مجموعة كشفية جديد" :action="route('group-type.insert')" method="POST"
        inputPlaceholder="ادخل اسم نوع المجموعة الكشفية" inputLabel="ادخل اسم نوع المجموعة الكشفية"
        submitText="إضافة نوع مجموعة كشفية" submitColor="blue" inputName="group_type_name" />
@endsection
