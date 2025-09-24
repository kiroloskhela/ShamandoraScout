@extends('layouts.app', ['pageTitle' => 'الجامعات'])

@section('content')
    <x-form-card title="اضافة جامعه جديدة" :action="route('university.insert')" method="POST" inputPlaceholder="ادخل اسم الجامعه"
        inputLabel="ادخل اسم الجامعه" submitText="إضافة الجامعه" submitColor="blue" inputName="university_name" />
@endsection
