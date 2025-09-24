@extends('layouts.app', ['pageTitle' => 'إضافة حي سكني' ?? ''])
@section('content')
    <x-form-card title="إضافة حي سكني جديد" :action="route('district.insert')" method="POST" inputPlaceholder="ادخل اسم الحي السكني"
        inputLabel="ادخل اسم الحي السكني" submitText="إضافة الحي السكني" submitColor="blue" inputName="district_name" />
@endsection
