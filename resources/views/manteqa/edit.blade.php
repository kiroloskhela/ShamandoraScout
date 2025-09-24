@extends('layouts.app' , ['pageTitle' => "تعديل منطقة سكنية"])
@section('content')

<x-form-card title="تعديل منطقة سكنية" :action="route('manteqa.update', $manteqa->ManteqaID)" method="PATCH"
    inputPlaceholder="ادخل اسم المنطقة السكنية" inputLabel="اسم المنطقة السكنية" submitText="تعديل منطقة سكنية"
    submitColor="emerald" inputName="manteqa_name" :inputValue="$manteqa->ManteqaName" />

@endsection