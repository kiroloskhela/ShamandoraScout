@extends('layouts.app', ['pageTitle' => __('Delete document')])

@section('content')
    <x-form-card :title="__('Delete document')" :action="route('secretary.destroy', $document->DocumentID)" method="DELETE" :inputValue="$document->DocumentName" inputPlaceholder="{{ __('Enter document name') }}"
        inputLabel="{{ __('Document name') }}" submitText="{{ __('Delete') }}" submitColor="red" inputName="document_name" />
@endsection
