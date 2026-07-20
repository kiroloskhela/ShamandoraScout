@extends('layouts.app', ['pageTitle' => __('Delete scout sector')])
@section('content')


<x-form-card :title="__('Delete scout sector')" :action="route('qetaa.destroy', $qetaa->QetaaID)" method="DELETE"
    inputPlaceholder="{{ __('Form label 724e6476') }}" inputLabel="{{ __('Sector name') }}" submitText="{{ __('Form label 3b9854e1') }}" submitColor="red" inputName="qetaa_name"
    :inputValue="$qetaa->QetaaName" />



@endsection