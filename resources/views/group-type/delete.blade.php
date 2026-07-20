@extends('layouts.app')
@section('content')
    <x-form-card title="{{ __('Form label 91522d78') }}" :action="route('group-type.destroy', $groupType->GroupTypeID)" method="DELETE"
        inputPlaceholder="{{ __('Form label 022739ff') }}" inputLabel="{{ __('Form label 022739ff') }}"
        submitText="{{ __('Form label 91522d78') }}" submitColor="red" inputName="group_type_name" :inputValue="$groupType->GroupTypeName" />
@endsection
