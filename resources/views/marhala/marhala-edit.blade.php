@extends('layouts.app', ['pageTitle' => __('Edit academic stage')])

@section('content')
    <x-form-card title="{{ __('Edit academic stage') }}" :action="route('marhala.update', $marhala->MarhalaID)" method="PATCH" :inputValue="$marhala->MarhalaName"
        inputPlaceholder="{{ __('Enter academic stage') }}" inputLabel="{{ __('Enter academic stage') }}" submitText="{{ __('Edit') }}" submitColor="emerald"
        inputName="marhala_name" />
@endsection
