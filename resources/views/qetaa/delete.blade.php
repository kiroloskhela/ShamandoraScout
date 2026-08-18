@extends('layouts.app', ['pageTitle' => __('Delete scout sector')])
@section('content')


<x-form-card :title="__('Delete scout sector')" :action="route('qetaa.destroy', $qetaa->QetaaID)" method="DELETE"
    inputPlaceholder="{{ __('Enter sector name') }}" inputLabel="{{ __('Sector name') }}" submitText="{{ __('Delete') }}" submitColor="red" inputName="qetaa_name"
    :inputValue="$qetaa->QetaaName" />



@endsection