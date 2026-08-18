@extends('layouts.app', ['pageTitle' => __('Delete residential district')])
@section('content')
    <x-form-card :title="__('Delete residential district')" :action="route('district.destroy', $district->DistrictID)" method="DELETE" inputPlaceholder="{{ __('Enter district name') }}"
        inputLabel="{{ __('Enter district name') }}" submitText="{{ __('Delete residential district') }}" submitColor="red" inputName="district_name"
        :inputValue="$district->DistrictName" />
@endsection
