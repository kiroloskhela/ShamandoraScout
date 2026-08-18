@extends('layouts.app', ['pageTitle' => __('Scout group types')])
@section('content')
    <x-form-card title="{{ __('Add group type') }}" :action="route('group-type.insert')" method="POST"
        inputPlaceholder="{{ __('Enter group type name') }}" inputLabel="{{ __('Enter group type name') }}"
        submitText="{{ __('Add group type') }}" submitColor="blue" inputName="group_type_name" />
@endsection
