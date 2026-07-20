@extends('layouts.app' , ['pageTitle' => __('Delete residential area')])
@section('content')


<x-form-card :title="__('Delete residential area')" :action="route('manteqa.destroy', $manteqa->ManteqaID)" method="DELETE"
    inputPlaceholder="{{ __('Form label ff17c5a8') }}" inputLabel="{{ __('Form label ff17c5a8') }}" submitText="{{ __('Form label e1e31f22') }}"
    submitColor="red" inputName="manteqa_name" :inputValue="$manteqa->ManteqaName" />

@endsection