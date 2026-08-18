@extends('layouts.app', ['pageTitle' => __('Faculties')])

@section('content')
    <x-form-card :title="__('Delete faculty name')" :action="route('faculty.destroy', $faculty->FacultyID)" method="DELETE" :inputValue="$faculty->FacultyName" inputPlaceholder="{{ __('Enter faculty name') }}"
        inputLabel="{{ __('Faculty name') }}" submitText="{{ __('Delete') }}" submitColor="red" pageTitle="__('Faculties')" />
@endsection
