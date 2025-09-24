@extends('layouts.app', ['pageTitle' => 'تعديل دور/مهمة' ?? ''])
@section('content')
    <x-form-card title="تعديل دور/مهمة" :action="route('role.update', $role->RoleID)" method="PATCH" inputPlaceholder="ادخل اسم الدور"
        inputLabel="ادخل اسم الدور" submitText="تعديل الدور/المهمة" submitColor="emerald" inputName="role_name"
        :inputValue="$role->RoleName" />
@endsection
