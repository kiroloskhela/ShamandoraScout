@extends('layouts.app', ['pageTitle' => __('Edit rank (tree)')])
@section('content')
    <x-form-card title="{{ __('Edit rank') }}" :action="route('rotab.update', $rotab->RotbaID)" method="PATCH" :inputValue="$rotab->RotbaName" inputPlaceholder="{{ __('Enter rank name') }}"
        inputLabel="{{ __('Rank name') }}" submitText="{{ __('Edit') }}" submitColor="emerald" inputName="rotba_name" />
@endsection
