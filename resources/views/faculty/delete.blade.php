@extends('layouts.app', ['pageTitle' => 'الكليات'])

@section('content')
    <x-form-card title="مسح اسم الكلية" :action="route('faculty.destroy', $faculty->FacultyID)" method="DELETE" :inputValue="$faculty->FacultyName" inputPlaceholder="ادخل اسم الكلية"
        inputLabel="تعديل اسم الكلية" submitText="مسح" submitColor="red" pageTitle="الكليات" />
@endsection
