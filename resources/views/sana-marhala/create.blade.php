@extends('layouts.app', ['pageTitle' => __('Add academic stage')])
@section('content')

<x-form-card :title="__('Add academic stage')" :action="route('sana-marhala.insert')" method="POST"
    inputPlaceholder="{{ __('Form label c98d1154') }}" inputLabel="{{ __('Form label 9ede0a2d') }}" submitText="{{ __('Add academic stage') }}"
    submitColor="blue" inputName="sana_marhala_name" />

@endsection