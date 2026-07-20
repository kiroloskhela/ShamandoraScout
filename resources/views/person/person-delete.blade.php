@extends('layouts.app', ['pageTitle' => __('Delete person')])

@section('content')
    <x-form-card title="{{ __('Form label 4427926f') }}" :action="route('person.destroy', $person->PersonID)" method="DELETE" :inputValue="$person->ShamandoraCode" inputPlaceholder="{{ __('Form label 13ca49c2') }}"
        inputLabel="{{ __('Form label 4427926f') }}" submitText="{{ __('Delete') }}" submitColor="red" />
@endsection
