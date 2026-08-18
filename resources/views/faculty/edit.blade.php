@extends('layouts.app', ['pageTitle' => __('Faculties')])
@section('content')
    <x-form-card :title="__('Edit faculty name')" :action="route('faculty.update', $faculty->FacultyID)" method="PATCH" :inputValue="$faculty->FacultyName" inputPlaceholder="{{ __('Enter faculty name') }}"
        inputLabel="{{ __('Faculty name') }}" submitText="{{ __('Edit') }}" submitColor="emerald" pageTitle="__('Faculties')" inputName="faculty_name" />
@endsection
