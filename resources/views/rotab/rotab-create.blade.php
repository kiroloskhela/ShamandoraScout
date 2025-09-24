@extends('layouts.app', ['pageTitle' => 'إضافة رتبة جديدة'])

@section('content')
    <x-form-card title="اضافة رتبة جديدة" :action="route('rotab.insert')" method="POST" inputPlaceholder="ادخل اسم الرتبة"
        inputLabel="ادخل اسم الرتبة" submitText="إضافة الرتبة" submitColor="blue" inputName="rotba_name" />
@endsection
