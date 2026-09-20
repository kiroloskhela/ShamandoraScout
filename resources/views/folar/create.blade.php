@extends('layouts.app', ['pageTitle' => __('Scout scarves')])

@section('content')
    <x-form-card title="{{ __('Add new scout scarf') }}" :action="route('folar.insert')" method="POST"
        inputPlaceholder="{{ __('Enter scout scarf') }}" inputLabel="{{ __('Enter scout scarf') }}"
        submitText="{{ __('Add scout scarf') }}" submitColor="blue" inputName="folar_name" />
@endsection
