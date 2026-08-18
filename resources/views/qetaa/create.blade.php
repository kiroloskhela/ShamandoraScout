@extends('layouts.app', ['pageTitle' => __('Add scout sector')])
@section('content')

<x-form-card :title="__('Add scout sector')" :action="route('qetaa.insert')" method="POST" inputPlaceholder="{{ __('Enter sector name') }}"
    inputLabel="{{ __('Sector name') }}" submitText="{{ __('Add scout sector') }}" submitColor="blue" inputName="qetaa_name" />

@endsection