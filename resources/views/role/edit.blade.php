@extends('layouts.app', ['pageTitle' => __('Edit role/duty')])
@section('content')
    <x-form-card :title="__('Edit role/duty')" :action="route('role.update', $role->RoleID)" method="PATCH" inputPlaceholder="{{ __('Enter role name') }}"
        inputLabel="{{ __('Enter role name') }}" submitText="{{ __('Edit role/duty') }}" submitColor="emerald" inputName="role_name"
        :inputValue="$role->RoleName" />
@endsection
