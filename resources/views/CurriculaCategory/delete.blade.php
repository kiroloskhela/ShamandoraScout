@extends('layouts.app', ['pageTitle' => __('Delete category')])

@section('content')
    <x-form-card title="{{ __('Delete category') }}" :action="route('CurriculaCategory.destroy', $CurriculaCategory->CurriculaCategoryID)" method="DELETE" :inputValue="$CurriculaCategory->CurriculaCategoryName" inputPlaceholder="{{ __('Enter category name') }}"
        inputLabel="{{ __('Category name') }}" submitText="{{ __('Delete') }}" submitColor="red" pageTitle="__('Curriculum categories')"
        inputName="CurriculaCategoryName" />
@endsection
