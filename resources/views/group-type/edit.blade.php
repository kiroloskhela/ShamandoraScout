@extends('layouts.app')

@section('content')
    <x-form-card title="{{ __('Edit group type') }}" :action="route('group-type.update', $groupType->GroupTypeID)" method="PATCH"
        inputPlaceholder="{{ __('Enter group type name') }}" inputLabel="{{ __('Enter group type name') }}"
        submitText="{{ __('Edit group type') }}" submitColor="green" inputName="group_type_name" :inputValue="$groupType->GroupTypeName" />
@endsection
