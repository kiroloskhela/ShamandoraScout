@extends('layouts.app', ['pageTitle' => __('Delete detailed academic stage')])
@section('content')


<x-form-card :title="__('Delete detailed academic stage')" :action="route('sana-marhala.destroy', $sana->SanaMarhalaID)"
    method="DELETE" inputPlaceholder="{{ __('Enter academic stage name') }}" inputLabel="{{ __('Academic stage') }}" submitText="{{ __('Delete') }}"
    submitColor="red" inputName="sana_marhala_name" :inputValue="$sana->SanaMarhalaName" />

@endsection