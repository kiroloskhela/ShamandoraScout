@extends('layouts.app', ['pageTitle' => __('Delete person')])

@section('content')
    <x-form-card title="{{ __('Delete person') }}" :action="route('person.destroy', $person->PersonID)" method="DELETE" :inputValue="$person->ShamandoraCode" inputPlaceholder="{{ __('Enter person name') }}"
        inputLabel="{{ __('Delete person') }}" submitText="{{ __('Delete') }}" submitColor="red" />
@endsection
