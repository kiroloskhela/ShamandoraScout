@extends('layouts.app', ['pageTitle' => __('Delete document')])

@section('content')
    <x-form-card :title="__('Delete document')" :action="route('secretary.destroy', $document->DocumentID)" method="DELETE" :inputValue="$document->DocumentName" inputPlaceholder="{{ __('Form label f0dc4572') }}"
        inputLabel="{{ __('Form label b1920f67') }}" submitText="{{ __('Delete') }}" submitColor="red" inputName="document_name" />
@endsection
