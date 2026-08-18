@extends('layouts.app')
@section('content')
    <x-form-card title="{{ __('Edit document') }}" :action="route('secretary.update', $document->DocumentID)" method="PATCH" :inputValue="$document->DocumentName" inputPlaceholder="{{ __('Enter document name') }}"
        inputLabel="{{ __('Document name') }}" submitText="{{ __('Edit') }}" submitColor="green" inputName="document_name" />
@endsection
