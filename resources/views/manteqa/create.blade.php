@extends('layouts.app', ['pageTitle' => __('Add residential area')])
@section('content')
    <x-form-card :title="__('Add residential area')" :action="route('manteqa.insert')" method="POST" inputPlaceholder="{{ __('Form label ff17c5a8') }}"
        inputLabel="{{ __('District name') }}" submitText="{{ __('Add residential area') }}" submitColor="blue" inputName="manteqa_name" />
@endsection
