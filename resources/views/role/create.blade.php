@extends('layouts.app', ['pageTitle' => __('Add role/duty')])
@section('content')
    <x-form-card :title="__('Add role/duty')" :action="route('role.insert')" method="POST" inputPlaceholder="{{ __('Enter role/duty name') }}"
        inputLabel="{{ __('Role/task name') }}" submitText="{{ __('Add role/duty') }}" submitColor="blue" inputName="role_name" />
@endsection
