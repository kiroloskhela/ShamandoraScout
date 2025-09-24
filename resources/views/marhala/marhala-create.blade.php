@extends('layouts.app' , ['pageTitle' => "اضافة المرحلة الدراسية"])

@section('content')

<x-form-card title="اضافة مرحله دراسية" :action="route('marhala.insert')" method="POST"
    inputPlaceholder="ادخل مرحله دراسية" inputLabel="ادخل مرحله دراسية" submitText="إضافة مرحله دراسية "
    submitColor="blue" inputName="marhala_name"/>

@endsection