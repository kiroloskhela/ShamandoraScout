@extends('layouts.app', ['pageTitle' => 'مسح قسم'])

@section('content')
    <x-form-card title="مسح القسم" :action="route('CurriculaCategory.destroy', $CurriculaCategory->CurriculaCategoryID)" method="DELETE" :inputValue="$CurriculaCategory->CurriculaCategoryName" inputPlaceholder="ادخل اسم القسم"
        inputLabel="مسح اسم القسم" submitText="مسح" submitColor="red" pageTitle="اقسام المناهج"
        inputName="CurriculaCategoryName" />
@endsection
