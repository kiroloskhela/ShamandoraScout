@extends('layouts.app', ['pageTitle' => __('Delete academic stage')])
@section('content')
    <x-form-card title="{{ __('Delete academic stage') }}" :action="route('marhala.destroy', $marhala->MarhalaID)" method="DELETE" :inputValue="$marhala->MarhalaName"
        inputPlaceholder="{{ __('Form label 6877c3a5') }}" inputLabel="{{ __('Delete academic stage') }}" submitText="{{ __('Delete') }}" submitColor="red" />
@endsection
