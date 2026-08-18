@extends('layouts.app', ['pageTitle' => __('Add location')])
@section('content')
    <x-form-card :title="__('Add location')" :action="route('locations.insert')" method="POST" inputPlaceholder="{{ __('Enter location name') }}"
        inputLabel="{{ __('Area name') }}" submitText="{{ __('Add area') }}" submitColor="blue" inputName="LocationName" />
@endsection
