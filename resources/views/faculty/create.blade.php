@extends('layouts.app', ['pageTitle' => __('Faculties')])

@section('content')
    <x-form-card :title="__('Add new faculty')" :action="route('faculty.insert')" method="POST" inputPlaceholder="{{ __('Form label 044d0538') }}"
        inputLabel="{{ __('Form label 044d0538') }}" submitText="{{ __('Form label fd8ccce6') }}" submitColor="blue" inputName="faculty_name" />
@endsection
