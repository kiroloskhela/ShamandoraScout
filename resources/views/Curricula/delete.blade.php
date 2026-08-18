@extends('layouts.app', ['pageTitle' => __('Delete lecture')])

@section('content')
    <x-form-card :title="__('Delete lecture')" :action="route('curricula.destroy', $curriculum->CurriculaID)" method="DELETE" :inputValue="$curriculum->CurriculaName" inputPlaceholder="{{ __('Enter lecture name') }}"
        inputLabel="{{ __('Lecture name') }}" submitText="{{ __('Delete') }}" submitColor="red" inputName="curricula_name" />
@endsection
