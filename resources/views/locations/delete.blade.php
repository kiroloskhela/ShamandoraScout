@extends('layouts.app', ['pageTitle' => __('Delete location')])
@section('content')
    <x-form-card :title="__('Delete location')" :action="route('locations.destroy', $location->LocationID)" method="DELETE" inputPlaceholder="{{ __('Form label 3752defe') }}"
        inputLabel="{{ __('Form label 3752defe') }}" submitText="{{ __('Form label 8519057b') }}" submitColor="red" inputName="LocationName" :inputValue="$location->LocationName" />
@endsection
