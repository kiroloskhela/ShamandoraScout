@extends('layouts.app' , ['pageTitle' => "إضافة منطقة سكنية"])
@section('content')

<x-form-card title="إضافة منطقة سكنية" :action="route('manteqa.insert')" method="POST"
    inputPlaceholder="ادخل اسم المنطقة السكنية" inputLabel="اسم المنطقة السكنية" submitText="إضافة منطقة سكنية"
    submitColor="blue" inputName="manteqa_name" />

@endsection