@extends('layouts.app', ['pageTitle' => __('Delete place')])
@section('content')
    <x-form-card :title="__('Delete place')" :action="route('place.destroy', $place->PlaceID)" method="DELETE" inputPlaceholder="{{ __('Form label 9f772c5f') }}"
        inputLabel="{{ __('Form label 9f772c5f') }}" submitText="{{ __('Form label e6f3d710') }}" submitColor="red" inputName="PlaceName" :inputValue="$place->PlaceName" />
@endsection
