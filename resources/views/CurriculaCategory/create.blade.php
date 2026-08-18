@extends('layouts.app', ['pageTitle' => __('Curriculum categories')])

@section('content')
    {{-- CREATE FORM - resources/views/faculty/create.blade.php --}}
    <x-form-card title="{{ __('Add a new category') }}" :action="route('CurriculaCategory.insert')" method="POST" inputPlaceholder="{{ __('Enter category name') }}"
        inputLabel="{{ __('Category name') }}" submitText="{{ __('Add category') }}" submitColor="blue" inputName="CurriculaCategoryName"
        {{-- THIS MUST BE SET --}} />
@endsection
