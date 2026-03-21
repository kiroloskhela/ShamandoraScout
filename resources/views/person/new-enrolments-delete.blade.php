@extends('layouts.app', ['pageTitle' => 'طلبات التسجيل'])

@section('content')
    <x-form-card title="مسح طلب تسجيل" :action="route('person.new-enrolments-destroy', $person->PersonID)" method="DELETE" :inputValue="$person->FullName" inputPlaceholder="ادخل اسم الشخص"
        inputLabel="تعديل اسم الشخص" submitText="مسح" submitColor="red" pageTitle="طلبات التسجيل" />
@endsection
