@extends('layouts.app', ['pageTitle' => __('Delete detailed academic stage')])
@section('content')


<x-form-card :title="__('Delete detailed academic stage')" :action="route('sana-marhala.destroy', $sana->SanaMarhalaID)"
    method="DELETE" inputPlaceholder="{{ __('Form label c98d1154') }}" inputLabel="{{ __('Form label 9ede0a2d') }}" submitText="{{ __('Form label 3b9854e1') }}"
    submitColor="red" inputName="sana_marhala_name" :inputValue="$sana->SanaMarhalaName" />

@endsection