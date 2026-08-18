@extends('layouts.app' , ['pageTitle' => __('Universities')])
@section('content')
<x-form-card title="{{ __('Edit university') }}" :action="route('university.update', $university->UniversityID)" method="PATCH"
    :inputValue="$university->UniversityName" inputPlaceholder="{{ __('Enter university name') }}" inputLabel="{{ __('University name') }}"
    submitText="{{ __('Edit') }}" submitColor="emerald" inputName="university_name" />
@endsection