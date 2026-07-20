@extends('layouts.app', ['pageTitle' => __('Progress badge certificate')])

@section('content')
    <x-form-card :title="__('Add progress badge certificate')" :action="route('betaka.insert')" method="POST"
        inputPlaceholder="{{ __('Enter progress badge certificate name') }}"
        inputLabel="{{ __('Enter progress badge certificate name') }}" submitText="{{ __('Add') }}" submitColor="blue"
        inputName="betaka_name" />
@endsection
