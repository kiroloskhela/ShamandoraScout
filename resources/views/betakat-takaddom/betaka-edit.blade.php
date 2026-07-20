@extends('layouts.app', ['pageTitle' => __('Progress badge certificate')])

@section('content')
    <x-form-card :title="__('Edit progress badge certificate')" :action="route('betaka.update', $betakat->EgazetBetakatTaqaddomID)" method="PATCH"
        :inputValue="$betakat->EgazetBetakatTaqaddomName" inputPlaceholder="{{ __('Enter progress badge certificate name') }}"
        inputLabel="{{ __('Enter progress badge certificate name') }}" submitText="{{ __('Edit') }}" submitColor="emerald"
        inputName="betaka_name" />
@endsection
