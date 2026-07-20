@extends('layouts.app', ['pageTitle' => __('Delete lecture')])

@section('content')
    <x-form-card :title="__('Delete lecture')" :action="route('curricula.destroy', $curriculum->CurriculaID)" method="DELETE" :inputValue="$curriculum->CurriculaName" inputPlaceholder="{{ __('Form label 42da938b') }}"
        inputLabel="{{ __('Form label 64b938f5') }}" submitText="{{ __('Delete') }}" submitColor="red" inputName="curricula_name" />
@endsection
