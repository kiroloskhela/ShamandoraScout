@extends('layouts.app', ['pageTitle' => __('Add residential area')])
@section('content')
    <x-form-card :title="__('Add residential area')" :action="route('manteqa.insert')" method="POST" inputPlaceholder="{{ __('Enter residential area name') }}"
        inputLabel="{{ __('District name') }}" submitText="{{ __('Add residential area') }}" submitColor="blue" inputName="manteqa_name" />
@endsection
