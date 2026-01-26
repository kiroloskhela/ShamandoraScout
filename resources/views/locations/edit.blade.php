@extends('layouts.app', ['pageTitle' => 'تعديل منطقة'])
@section('content')
    <x-form-card title="تعديل منطقة" :action="route('locations.updates', $location->LocationID)" method="PATCH" inputPlaceholder="ادخل اسم المنطقة"
        inputLabel="اسم المنطقة" submitText="تعديل منطقة" submitColor="emerald" inputName="LocationName" :inputValue="$location->LocationName" />
@endsection
