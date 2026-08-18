@extends('layouts.app', ['pageTitle' => __('Edit category')])
@section('content')
    <x-form-card :title="__('Edit category')" :action="route('CurriculaCategory.update', $CurriculaCategory->CurriculaCategoryID)" method="PATCH" :inputValue="$CurriculaCategory->CurriculaCategoryName" inputPlaceholder="{{ __('Enter category name') }}"
        inputLabel="{{ __('Category name') }}" submitText="{{ __('Edit') }}" submitColor="emerald" inputName="CurriculaCategoryName" />
@endsection
