@extends('layouts.app', ['pageTitle' => __('Edit detailed academic stage')])
@section('content')


<x-form-card :title="__('Edit detailed academic stage')" :action="route('sana-marhala.update', $sana->SanaMarhalaID)"
    method="PATCH" inputPlaceholder="{{ __('Form label c98d1154') }}" inputLabel="{{ __('Form label 9ede0a2d') }}"
    submitText="{{ __('Form label b5c71fd6') }}" submitColor="emerald" inputName="sana_marhala_name"
    :inputValue="$sana->SanaMarhalaName" />

@endsection