@extends('layouts.app', ['pageTitle' => __('Delete category')])

@section('content')
    <x-form-card title="{{ __('Delete category') }}" :action="route('CurriculaCategory.destroy', $CurriculaCategory->CurriculaCategoryID)" method="DELETE" :inputValue="$CurriculaCategory->CurriculaCategoryName" inputPlaceholder="{{ __('Enter category name') }}"
        inputLabel="{{ __('Form label 946a609e') }}" submitText="{{ __('Delete') }}" submitColor="red" pageTitle="__('Curriculum categories')"
        inputName="CurriculaCategoryName" />
@endsection
