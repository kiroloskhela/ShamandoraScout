@extends('layouts.app', ['pageTitle' => 'فصائل الدم'])

@section('content')
    {{-- CREATE FORM - resources/views/faculty/create.blade.php --}}
    <x-form-card title="اضافة فصيله دم جديدة" :action="route('blood.insert')" method="POST" inputPlaceholder="ادخل فصيله دم "
        inputLabel="ادخل فصيله دم " submitText="إضافة فصيله دم " submitColor="blue" inputName="blood_name"
        {{-- THIS MUST BE SET --}} />
@endsection
