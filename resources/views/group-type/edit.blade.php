@extends('layouts.app')

@section('content')
    <x-form-card title="{{ __('Form label 1c4fe558') }}" :action="route('group-type.update', $groupType->GroupTypeID)" method="PATCH"
        inputPlaceholder="{{ __('Form label 022739ff') }}" inputLabel="{{ __('Form label 022739ff') }}"
        submitText="{{ __('Form label 6b164f38') }}" submitColor="green" inputName="group_type_name" :inputValue="$groupType->GroupTypeName" />
@endsection
