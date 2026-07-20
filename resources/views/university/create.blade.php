@extends('layouts.app', ['pageTitle' => __('Universities')])

@section('content')
    <x-form-card title="{{ __('Add new university') }}" :action="route('university.insert')" method="POST" inputPlaceholder="{{ __('Enter university name') }}"
        inputLabel="{{ __('Enter university name') }}" submitText="{{ __('Form label 7eb96ca2') }}" submitColor="blue" inputName="university_name" />
@endsection
