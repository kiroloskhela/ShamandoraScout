@extends('layouts.app', ['pageTitle' => 'الكليات'])
@section('content')
    <x-form-card title="تعديل كليه" :action="route('faculty.update', $faculty->FacultyID)" method="PATCH" :inputValue="$faculty->FacultyName" inputPlaceholder="ادخل اسم الكلية"
        inputLabel="تعديل اسم الكلية" submitText="تعديل" submitColor="emerald" pageTitle="الكليات" inputName="faculty_name" />
@endsection
