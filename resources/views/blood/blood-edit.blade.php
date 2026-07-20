@extends('layouts.app', ['pageTitle' => __('Blood types')])
@section('content')
    <x-form-card title="{{ __('Edit blood type') }}" :action="route('blood.update', $blood->BloodTypeID)" method="PATCH" :inputValue="$blood->BloodTypeName" inputPlaceholder="{{ __('Enter blood type') }}"
        inputLabel="{{ __('Form label 32177db8') }}" submitText="{{ __('Edit') }}" submitColor="emerald" inputName="blood_name" />
@endsection
