@extends('layouts.app', ['pageTitle' => __('Edit residential district')])
@section('content')
    <x-form-card :title="__('Edit residential district')" :action="route('district.update', $district->DistrictID)" method="PATCH" inputPlaceholder="{{ __('Form label 0d0f993e') }}"
        inputLabel="{{ __('Form label 0d0f993e') }}" submitText="{{ __('Form label d9ebc60d') }}" submitColor="emerald" inputName="district_name"
        :inputValue="$district->DistrictName" />
@endsection
