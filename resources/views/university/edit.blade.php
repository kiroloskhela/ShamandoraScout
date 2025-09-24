@extends('layouts.app' , ['pageTitle' => "الجامعات"])
@section('content')
<x-form-card title="تعديل الجامعه" :action="route('university.update', $university->UniversityID)" method="PATCH"
    :inputValue="$university->UniversityName" inputPlaceholder="ادخل اسم الجامعه" inputLabel="تعديل اسم الجامعه"
    submitText="تعديل" submitColor="emerald" inputName="university_name" />
@endsection