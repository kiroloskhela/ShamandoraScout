@extends('layouts.app', ['pageTitle' => __('Blood types')])

@section('content')
    <x-form-card title="{{ __('Delete blood type') }}" :action="route('blood.destroy', $blood->BloodTypeID)" method="DELETE" :inputValue="$blood->BloodTypeName" inputPlaceholder="{{ __('Enter blood type') }}"
        inputLabel="{{ __('Delete blood type') }}" submitText="{{ __('Delete') }}" submitColor="red" />
@endsection
