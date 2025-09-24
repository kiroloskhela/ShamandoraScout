@extends('layouts.app', ['pageTitle' => 'تعديل كلمة السر'])

@section('content')
<x-form-card 
    title="تعديل كلمة السر للمستخدم:<br>{{ $user->FirstName }} {{ $user->SecondName }} {{ $user->ThirdName }} {{ $user->FourthName }}"
    :action="route('admin.passwords.update', $user->PersonID)"
    method="POST"
    inputType="password"
    :inputValue="''"
    inputPlaceholder="ادخل كلمة سر جديدة"
    inputLabel="كلمة السر الجديدة"
    submitText="تعديل كلمة السر"
    submitColor="emerald"
    inputName="password"
/>
@endsection
