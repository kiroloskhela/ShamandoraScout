@extends('layouts.app')
@section('content')
    <x-form-card title="{{ __('Edit document') }}" :action="route('secretary.update', $document->DocumentID)" method="PATCH" :inputValue="$document->DocumentName" inputPlaceholder="{{ __('Form label e72526e2') }}"
        inputLabel="{{ __('Form label c1b5e074') }}" submitText="{{ __('Edit') }}" submitColor="green" inputName="document_name" />
@endsection
