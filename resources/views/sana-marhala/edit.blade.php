@extends('layouts.app', ['pageTitle' => __('Edit detailed academic stage')])
@section('content')


<x-form-card :title="__('Edit detailed academic stage')" :action="route('sana-marhala.update', $sana->SanaMarhalaID)"
    method="PATCH" inputPlaceholder="{{ __('Enter academic stage name') }}" inputLabel="{{ __('Academic stage') }}"
    submitText="{{ __('Edit academic stage') }}" submitColor="emerald" inputName="sana_marhala_name"
    :inputValue="$sana->SanaMarhalaName" />

@endsection