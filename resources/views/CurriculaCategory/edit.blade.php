@extends('layouts.app', ['pageTitle' => 'تعديل قسم'])
@section('content')
    <x-form-card title="تعديل قسم" :action="route('CurriculaCategory.update', $CurriculaCategory->CurriculaCategoryID)" method="PATCH" :inputValue="$CurriculaCategory->CurriculaCategoryName" inputPlaceholder="ادخل اسم القسم"
        inputLabel="تعديل اسم القسم" submitText="تعديل" submitColor="emerald" inputName="CurriculaCategoryName" />
@endsection
