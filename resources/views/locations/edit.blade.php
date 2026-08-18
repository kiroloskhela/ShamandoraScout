@extends('layouts.app', ['pageTitle' => __('Edit location')])
@section('content')
    <x-form-card :title="__('Edit location')" :action="route('locations.updates', $location->LocationID)" method="PATCH" inputPlaceholder="{{ __('Enter location name') }}"
        inputLabel="{{ __('Area name') }}" submitText="{{ __('Edit location') }}" submitColor="emerald" inputName="LocationName" :inputValue="$location->LocationName" />
@endsection
