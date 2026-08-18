@extends('layouts.app', ['pageTitle' => __('Delete location')])
@section('content')
    <x-form-card :title="__('Delete location')" :action="route('locations.destroy', $location->LocationID)" method="DELETE" inputPlaceholder="{{ __('Enter location name') }}"
        inputLabel="{{ __('Enter location name') }}" submitText="{{ __('Delete location') }}" submitColor="red" inputName="LocationName" :inputValue="$location->LocationName" />
@endsection
