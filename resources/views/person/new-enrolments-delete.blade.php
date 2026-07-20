@extends('layouts.app', ['pageTitle' => __('Registration requests')])

@section('content')
    <x-form-card title="{{ __('Form label 0d8af284') }}" :action="route('person.new-enrolments-destroy', $person->PersonID)" method="DELETE" :inputValue="$person->FullName" inputPlaceholder="{{ __('Form label 13ca49c2') }}"
        inputLabel="{{ __('Form label c6c73acc') }}" submitText="{{ __('Delete') }}" submitColor="red" pageTitle="__('Registration requests')" />
@endsection
