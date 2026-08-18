@extends('layouts.app' , ['pageTitle' => __('Delete role/duty')])
@section('content')


<x-form-card :title="__('Delete role/duty')" :action="route('role.destroy', $role->RoleID)" method="DELETE"
    inputPlaceholder="{{ __('Enter role/duty name') }}" inputLabel="{{ __('Enter role/duty name') }}" submitText="{{ __('Delete role/duty') }}"
    submitColor="red" inputName="role_name" :inputValue="$role->RoleName" />

@endsection