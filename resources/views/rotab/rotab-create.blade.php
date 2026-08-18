@extends('layouts.app', ['pageTitle' => __('Add new rank')])

@section('content')
    <x-form-card title="{{ __('Add new rank') }}" :action="route('rotab.insert')" method="POST" inputPlaceholder="{{ __('Enter rank name') }}"
        inputLabel="{{ __('Enter rank name') }}" submitText="{{ __('Add rank') }}" submitColor="blue" inputName="rotba_name" />
@endsection
