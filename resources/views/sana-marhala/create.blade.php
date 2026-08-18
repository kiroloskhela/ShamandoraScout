@extends('layouts.app', ['pageTitle' => __('Add academic stage')])
@section('content')

<x-form-card :title="__('Add academic stage')" :action="route('sana-marhala.insert')" method="POST"
    inputPlaceholder="{{ __('Enter academic stage name') }}" inputLabel="{{ __('Academic stage') }}" submitText="{{ __('Add academic stage') }}"
    submitColor="blue" inputName="sana_marhala_name" />

@endsection