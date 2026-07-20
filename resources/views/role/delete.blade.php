@extends('layouts.app' , ['pageTitle' => __('Delete role/duty')])
@section('content')


<x-form-card :title="__('Delete role/duty')" :action="route('role.destroy', $role->RoleID)" method="DELETE"
    inputPlaceholder="{{ __('Form label f9e93980') }}" inputLabel="{{ __('Form label f9e93980') }}" submitText="{{ __('Form label 0af9c98a') }}"
    submitColor="red" inputName="role_name" :inputValue="$role->RoleName" />

@endsection