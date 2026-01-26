@extends('layouts.app', ['pageTitle' => 'حذف منطقة'])
@section('content')
    <x-form-card title="حذف منطقة" :action="route('locations.destroy', $location->LocationID)" method="DELETE" inputPlaceholder="ادخل اسم المنطقة"
        inputLabel="ادخل اسم المنطقة" submitText="حذف المنطقة" submitColor="red" inputName="LocationName" :inputValue="$location->LocationName" />
@endsection
