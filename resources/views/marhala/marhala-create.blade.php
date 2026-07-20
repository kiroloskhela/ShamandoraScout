@extends('layouts.app', ['pageTitle' => __('Add academic stage (alt)')])

@section('content')
    <x-form-card title="{{ __('Add academic stage') }}" :action="route('marhala.insert')" method="POST" inputPlaceholder="{{ __('Enter academic stage') }}"
        inputLabel="{{ __('Enter academic stage') }}" submitText="{{ __('Add academic stage') }}" submitColor="blue" inputName="marhala_name" />
@endsection
