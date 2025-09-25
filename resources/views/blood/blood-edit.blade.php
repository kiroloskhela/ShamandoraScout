@extends('layouts.app', ['pageTitle' => 'فصائل الدم'])
@section('content')
    <x-form-card title="تعديل فصيله دم" :action="route('blood.update', $blood->BloodTypeID)" method="PATCH" :inputValue="$blood->BloodTypeName" inputPlaceholder="ادخل  فصيله دم"
        inputLabel="تعديل  فصيله دم" submitText="تعديل" submitColor="emerald" inputName="blood_name" />
@endsection
