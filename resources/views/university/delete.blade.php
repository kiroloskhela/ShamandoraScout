@extends('layouts.app' , ['pageTitle' => "الجامعات"])

@section('content')
<x-form-card title="مسح اسم الجامعه" :action="route('university.destroy', $university->UniversityID)" method="DELETE"
    :inputValue="$university->UniversityName" inputPlaceholder="ادخل اسم الجامعه" inputLabel="مسح اسم الجامعه"
    submitText="مسح" submitColor="red" inputName="university_name" />

@endsection