@extends('layouts.app', ['pageTitle' => 'إضافة مرحلة دراسية'])
@section('content')

<x-form-card title="إضافة مرحلة دراسية" :action="route('sana-marhala.insert')" method="POST"
    inputPlaceholder="ادخل اسم المرحلة الدراسية" inputLabel="اسم المرحلة الدراسية" submitText="إضافة مرحلة دراسية"
    submitColor="blue" inputName="sana_marhala_name" />

@endsection