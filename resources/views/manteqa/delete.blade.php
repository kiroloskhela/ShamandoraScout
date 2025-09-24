@extends('layouts.app' , ['pageTitle' => "حذف منطقة سكنية"])
@section('content')


<x-form-card title="حذف منطقة سكنية" :action="route('manteqa.destroy', $manteqa->ManteqaID)" method="DELETE"
    inputPlaceholder="ادخل اسم المنطقة السكنية" inputLabel="ادخل اسم المنطقة السكنية" submitText="حذف المنطقة السكنية"
    submitColor="red" inputName="manteqa_name" :inputValue="$manteqa->ManteqaName" />

@endsection