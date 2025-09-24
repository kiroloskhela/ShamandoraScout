@extends('layouts.app', ['pageTitle' => 'تعديل المرحلة الدراسية'])

@section('content')
    <x-form-card title="تعديل مرحله دراسية" :action="route('marhala.update', $marhala->MarhalaID)" method="PATCH" :inputValue="$marhala->MarhalaName"
        inputPlaceholder="ادخل مرحله دراسية" inputLabel="ادخل مرحله دراسية" submitText="تعديل" submitColor="emerald"
        inputName="marhala_name" />
@endsection
