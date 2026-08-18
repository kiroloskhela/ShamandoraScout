@extends('layouts.app', ['pageTitle' => __('Delete place')])
@section('content')
    <x-form-card :title="__('Delete place')" :action="route('place.destroy', $place->PlaceID)" method="DELETE" inputPlaceholder="{{ __('Enter place name') }}"
        inputLabel="{{ __('Enter place name') }}" submitText="{{ __('Delete place') }}" submitColor="red" inputName="PlaceName" :inputValue="$place->PlaceName" />
@endsection
