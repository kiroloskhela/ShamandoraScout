@extends('layouts.app', ['pageTitle' => 'اقسام المناهج'])

@section('content')
    {{-- CREATE FORM - resources/views/faculty/create.blade.php --}}
    <x-form-card title="اضافة قسم جديد" :action="route('CurriculaCategory.insert')" method="POST" inputPlaceholder="ادخل اسم القسم"
        inputLabel="اسم القسم" submitText="إضافة قسم" submitColor="blue" inputName="CurriculaCategoryName"
        {{-- THIS MUST BE SET --}} />
@endsection
