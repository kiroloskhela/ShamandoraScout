@extends('layouts.app' , ['pageTitle' => __('Delete residential area')])
@section('content')


<x-form-card :title="__('Delete residential area')" :action="route('manteqa.destroy', $manteqa->ManteqaID)" method="DELETE"
    inputPlaceholder="{{ __('Enter residential area name') }}" inputLabel="{{ __('Enter residential area name') }}" submitText="{{ __('Delete residential area') }}"
    submitColor="red" inputName="manteqa_name" :inputValue="$manteqa->ManteqaName" />

@endsection