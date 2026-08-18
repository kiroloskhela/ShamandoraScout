@extends('layouts.app', ['pageTitle' => __('Delete event type')])

@section('content')
    <x-form-card :title="__('Delete event type')" :action="route('event-type.destroy', $eventType->EventTypeID)" method="DELETE" :inputValue="$eventType->EventTypeName"
        inputPlaceholder="{{ __('Enter event type name') }}" inputLabel="{{ __('Event type name') }}" submitText="{{ __('Delete') }}" submitColor="red"
        pageTitle="__('Manage occasions')" />
@endsection
