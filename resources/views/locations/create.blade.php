@extends('layouts.app', ['pageTitle' => 'إضافة منطقة'])
@section('content')
    <x-form-card title="إضافة منطقة" :action="route('locations.insert')" method="POST" inputPlaceholder="ادخل اسم المنطقة"
        inputLabel="اسم المنطقة" submitText="إضافة منطقة" submitColor="blue" inputName="LocationName" />
@endsection
