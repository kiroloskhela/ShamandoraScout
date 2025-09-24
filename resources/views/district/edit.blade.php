@extends('layouts.app', ['pageTitle' => 'تعديل حي سكني' ?? ''])
@section('content')
    <x-form-card title="تعديل حي سكني" :action="route('district.update', $district->DistrictID)" method="PATCH" inputPlaceholder="ادخل اسم الحي"
        inputLabel="ادخل اسم الحي" submitText="تعديل الحي السكني" submitColor="emerald" inputName="district_name"
        :inputValue="$district->DistrictName" />
@endsection
