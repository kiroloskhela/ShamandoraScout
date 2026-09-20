@extends('layouts.app', ['pageTitle' => __('Scout scarves')])

@section('content')
    <x-form-card title="{{ __('Edit scout scarf') }}" :action="route('folar.update', $folar->FolarID)" method="PATCH"
        :inputValue="$folar->FolarName" inputPlaceholder="{{ __('Enter scout scarf') }}"
        inputLabel="{{ __('Scout scarf') }}" submitText="{{ __('Edit') }}" submitColor="emerald" inputName="folar_name" />
@endsection
