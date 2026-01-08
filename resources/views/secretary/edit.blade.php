@extends('layouts.app')
@section('content')
    <x-form-card title="تعديل المستند" :action="route('secretary.update', $document->DocumentID)" method="PATCH" :inputValue="$document->DocumentName" inputPlaceholder="ادخل اسم المستند"
        inputLabel="تعديل اسم المستند" submitText="تعديل" submitColor="green" inputName="document_name" />
@endsection
