@extends('layouts.app', ['pageTitle' => __('Blood types')])

@section('content')
    {{-- CREATE FORM - resources/views/faculty/create.blade.php --}}
    <x-form-card title="{{ __('Add new blood type') }}" :action="route('blood.insert')" method="POST" inputPlaceholder="{{ __('Enter blood type') }}"
        inputLabel="{{ __('Enter blood type') }}" submitText="{{ __('Add blood type') }}" submitColor="blue" inputName="blood_name"
        {{-- THIS MUST BE SET --}} />
@endsection
