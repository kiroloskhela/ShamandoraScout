@extends('layouts.app', ['pageTitle' => __('Faculties')])

@section('content')
    <x-form-card :title="__('Add new faculty')" :action="route('faculty.insert')" method="POST" inputPlaceholder="{{ __('Enter faculty name') }}"
        inputLabel="{{ __('Enter faculty name') }}" submitText="{{ __('Add faculty') }}" submitColor="blue" inputName="faculty_name" />
@endsection
