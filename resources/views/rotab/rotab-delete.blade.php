@extends('layouts.app', ['pageTitle' => __('Delete rank name')])

@section('content')
    <x-form-card :title="__('Delete rank name')" :action="route('rotab.destroy', $rotab->RotbaID)" method="DELETE" :inputValue="$rotab->RotbaName" inputPlaceholder="{{ __('Enter rank name') }}"
        inputLabel="{{ __('Delete rank name') }}" submitText="{{ __('Delete') }}" submitColor="red" inputName="rotba_name" />
@endsection
