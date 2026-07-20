@extends('layouts.app', ['pageTitle' => __('Delete occasion')])

@section('content')
    <x-form-card :title="__('Delete occasion')" :action="route('event.destroy', $event->EventID)" method="DELETE" :inputValue="$event->EventName" inputPlaceholder="{{ __('Form label 80be41f2') }}"
        inputLabel="{{ __('Form label fa271015') }}" submitText="{{ __('Delete') }}" submitColor="red" pageTitle="__('Manage occasions')" inputName="event_name" />
@endsection
