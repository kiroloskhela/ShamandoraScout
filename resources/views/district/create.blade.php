@extends('layouts.app', ['pageTitle' => __('Add residential district')])
@section('content')
    <x-form-card title="{{ __('Add new residential district') }}" :action="route('district.insert')" method="POST" inputPlaceholder="{{ __('Enter district name') }}"
        inputLabel="{{ __('Enter district name') }}" submitText="{{ __('Add residential district') }}" submitColor="blue" inputName="district_name" />
@endsection
