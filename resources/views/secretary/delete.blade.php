@extends('layouts.app', ['pageTitle' => 'مسح المستند'])

@section('content')
    <x-form-card title="مسح المستند" :action="route('secretary.destroy', $document->DocumentID)" method="DELETE" :inputValue="$document->DocumentName" inputPlaceholder="ادخل المستند"
        inputLabel="مسح اسم المستند" submitText="مسح" submitColor="red" inputName="document_name" />
@endsection
