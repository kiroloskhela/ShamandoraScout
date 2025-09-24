@extends('layouts.app' , ['pageTitle' => "الكليات"])

@section('content')

<x-form-card title="اضافة كليه جديدة" :action="route('faculty.insert')" method="POST" inputPlaceholder="ادخل اسم الكلية"
    inputLabel="ادخل اسم الكلية" submitText="إضافة الكلية" submitColor="blue" inputName="faculty_name"
    />

@endsection