@extends('layouts.app', ['pageTitle' => __('Faculties')])

@section('content')
    <x-form-card :title="__('Delete faculty name')" :action="route('faculty.destroy', $faculty->FacultyID)" method="DELETE" :inputValue="$faculty->FacultyName" inputPlaceholder="{{ __('Form label 044d0538') }}"
        inputLabel="{{ __('Form label f7280b7f') }}" submitText="{{ __('Delete') }}" submitColor="red" pageTitle="__('Faculties')" />
@endsection
