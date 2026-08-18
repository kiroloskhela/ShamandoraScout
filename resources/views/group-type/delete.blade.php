@extends('layouts.app')
@section('content')
    <x-form-card title="{{ __('Delete group type') }}" :action="route('group-type.destroy', $groupType->GroupTypeID)" method="DELETE"
        inputPlaceholder="{{ __('Enter group type name') }}" inputLabel="{{ __('Enter group type name') }}"
        submitText="{{ __('Delete group type') }}" submitColor="red" inputName="group_type_name" :inputValue="$groupType->GroupTypeName" />
@endsection
