@extends('layouts.app', ['pageTitle' => 'إضافة دور/مهمة' ?? ''])
@section('content')
    <x-form-card title="إضافة دور/مهمة" :action="route('role.insert')" method="POST" inputPlaceholder="ادخل اسم الدور/المهمة"
        inputLabel="اسم الدور/المهمة" submitText="إضافة دور/مهمة" submitColor="blue" inputName="role_name" />
@endsection
