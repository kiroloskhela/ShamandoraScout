@extends('layouts.app', ['pageTitle' => 'حذف حي سكني' ?? ''])
@section('content')
    <x-form-card title="حذف حي سكني" :action="route('district.destroy', $district->DistrictID)" method="DELETE" inputPlaceholder="ادخل اسم الحي السكني"
        inputLabel="ادخل اسم الحي السكني" submitText="حذف الحي السكني" submitColor="red" inputName="district_name"
        :inputValue="$district->DistrictName" />
@endsection
