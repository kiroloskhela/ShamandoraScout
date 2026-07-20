@extends('layouts.app', ['pageTitle' => __('Faculties')])
@section('content')
    <x-form-card :title="__('Edit faculty name')" :action="route('faculty.update', $faculty->FacultyID)" method="PATCH" :inputValue="$faculty->FacultyName" inputPlaceholder="{{ __('Form label 044d0538') }}"
        inputLabel="{{ __('Form label f7280b7f') }}" submitText="{{ __('Edit') }}" submitColor="emerald" pageTitle="__('Faculties')" inputName="faculty_name" />
@endsection
