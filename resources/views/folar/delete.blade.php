@extends('layouts.app', ['pageTitle' => __('Scout scarves')])

@section('content')
    <x-form-card title="{{ __('Delete scout scarf') }}" :action="route('folar.destroy', $folar->FolarID)" method="DELETE"
        :inputValue="$folar->FolarName" inputPlaceholder="{{ __('Enter scout scarf') }}"
        inputLabel="{{ __('Delete scout scarf') }}" submitText="{{ __('Delete') }}" submitColor="red" />
@endsection
