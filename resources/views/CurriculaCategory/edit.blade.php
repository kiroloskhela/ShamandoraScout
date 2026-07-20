@extends('layouts.app', ['pageTitle' => __('Edit category')])
@section('content')
    <x-form-card :title="__('Edit category')" :action="route('CurriculaCategory.update', $CurriculaCategory->CurriculaCategoryID)" method="PATCH" :inputValue="$CurriculaCategory->CurriculaCategoryName" inputPlaceholder="{{ __('Enter category name') }}"
        inputLabel="{{ __('Form label ba040185') }}" submitText="{{ __('Edit') }}" submitColor="emerald" inputName="CurriculaCategoryName" />
@endsection
