@extends('layouts.app', ['pageTitle' => __('Edit role/duty')])
@section('content')
    <x-form-card :title="__('Edit role/duty')" :action="route('role.update', $role->RoleID)" method="PATCH" inputPlaceholder="{{ __('Form label f6df9b18') }}"
        inputLabel="{{ __('Form label f6df9b18') }}" submitText="{{ __('Form label 4d3d5ce3') }}" submitColor="emerald" inputName="role_name"
        :inputValue="$role->RoleName" />
@endsection
