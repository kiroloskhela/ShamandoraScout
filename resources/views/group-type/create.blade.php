@extends('layouts.app', ['pageTitle' => __('Scout group types')])
@section('content')
    <x-form-card title="{{ __('Form label 486bdcb2') }}" :action="route('group-type.insert')" method="POST"
        inputPlaceholder="{{ __('Form label 022739ff') }}" inputLabel="{{ __('Form label 022739ff') }}"
        submitText="{{ __('Form label a3243970') }}" submitColor="blue" inputName="group_type_name" />
@endsection
