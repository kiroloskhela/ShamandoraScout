@extends('layouts.app', ['pageTitle' => __('Edit scout sector')])
@section('content')

<x-form-card :title="__('Edit scout sector')" :action="route('qetaa.update', $qetaa->QetaaID)" method="PATCH"
    inputPlaceholder="{{ __('Form label 724e6476') }}" inputLabel="{{ __('Sector name') }}" submitText="{{ __('Edit') }}" submitColor="emerald"
    inputName="qetaa_name" :inputValue="$qetaa->QetaaName" />

@endsection