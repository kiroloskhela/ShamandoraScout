@extends('layouts.app', ['pageTitle' => __('Add scout sector')])
@section('content')

<x-form-card :title="__('Add scout sector')" :action="route('qetaa.insert')" method="POST" inputPlaceholder="{{ __('Form label 724e6476') }}"
    inputLabel="{{ __('Sector name') }}" submitText="{{ __('Form label c3dc3cc0') }}" submitColor="blue" inputName="qetaa_name" />

@endsection