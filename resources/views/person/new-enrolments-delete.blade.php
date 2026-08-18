@extends('layouts.app', ['pageTitle' => __('Registration requests')])

@section('content')
    <x-form-card title="{{ __('Delete enrolment request') }}" :action="route('person.new-enrolments-destroy', $person->PersonID)" method="DELETE" :inputValue="$person->FullName" inputPlaceholder="{{ __('Enter person name') }}"
        inputLabel="{{ __('Person name') }}" submitText="{{ __('Delete') }}" submitColor="red" pageTitle="__('Registration requests')" />
@endsection
