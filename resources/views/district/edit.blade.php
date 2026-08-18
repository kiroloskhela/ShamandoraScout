@extends('layouts.app', ['pageTitle' => __('Edit residential district')])
@section('content')
    <x-form-card :title="__('Edit residential district')" :action="route('district.update', $district->DistrictID)" method="PATCH" inputPlaceholder="{{ __('Enter district name') }}"
        inputLabel="{{ __('Enter district name') }}" submitText="{{ __('Edit residential district') }}" submitColor="emerald" inputName="district_name"
        :inputValue="$district->DistrictName" />
@endsection
