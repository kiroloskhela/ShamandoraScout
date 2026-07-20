@extends('layouts.app', ['pageTitle' => __('Edit residential area')])
@section('content')
    <x-form-card :title="__('Edit residential area')" :action="route('manteqa.update', $manteqa->ManteqaID)" method="PATCH" inputPlaceholder="{{ __('Form label ff17c5a8') }}"
        inputLabel="{{ __('District name') }}" submitText="{{ __('Edit residential area') }}" submitColor="emerald" inputName="manteqa_name"
        :inputValue="$manteqa->ManteqaName" />
@endsection
