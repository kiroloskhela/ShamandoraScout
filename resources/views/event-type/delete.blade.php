@extends('layouts.app', ['pageTitle' => __('Delete event type')])

@section('content')
    <x-form-card :title="__('Delete event type')" :action="route('event-type.destroy', $eventType->EventTypeID)" method="DELETE" :inputValue="$eventType->EventTypeName"
        inputPlaceholder="{{ __('Enter event type name') }}" inputLabel="{{ __('Form label 06f024e7') }}" submitText="{{ __('Delete') }}" submitColor="red"
        pageTitle="__('Manage occasions')" />
@endsection
