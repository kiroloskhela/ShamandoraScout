@extends('layouts.app', ['pageTitle' => __('Edit password')])

@section('content')
    <x-form-card
        title="{{ __('Edit password for user: :name', ['name' => trim(($user->FirstName ?? '') . ' ' . ($user->SecondName ?? '') . ' ' . ($user->ThirdName ?? '') . ' ' . ($user->FourthName ?? ''))]) }}"
        :action="route('admin.passwords.update', $user->PersonID)" method="POST" inputType="password" :inputValue="''"
        inputPlaceholder="{{ __('Enter new password') }}"
        inputLabel="{{ __('New password') }}" submitText="{{ __('Edit password') }}" submitColor="emerald"
        inputName="password" />
@endsection
