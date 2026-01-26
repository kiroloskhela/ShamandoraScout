@extends('layouts.app', ['pageTitle' => 'حذف مكان'])
@section('content')
    <x-form-card title="حذف مكان" :action="route('place.destroy', $place->PlaceID)" method="DELETE" inputPlaceholder="ادخل اسم المكان"
        inputLabel="ادخل اسم المكان" submitText="حذف المكان" submitColor="red" inputName="PlaceName" :inputValue="$place->PlaceName" />
@endsection
