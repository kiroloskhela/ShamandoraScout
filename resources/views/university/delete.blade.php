@extends('layouts.app', ['pageTitle' => __('Universities')])

@section('content')
    <x-form-card title="{{ __('Delete university name') }}" :action="route('university.destroy', $university->UniversityID)" method="DELETE" :inputValue="$university->UniversityName"
        inputPlaceholder="{{ __('Enter university name') }}" inputLabel="{{ __('Delete university name') }}" submitText="{{ __('Delete') }}" submitColor="red"
        inputName="university_name" />
@endsection
