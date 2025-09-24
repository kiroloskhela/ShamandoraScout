@extends('layouts.app', ['pageTitle' => 'تعديل الرتبة'])
@section('content')
    <x-form-card title="تعديل الرتبه" :action="route('rotab.update', $rotab->RotbaID)" method="PATCH" :inputValue="$rotab->RotbaName" inputPlaceholder="ادخل اسم الرتبه"
        inputLabel="تعديل اسم الرتبه" submitText="تعديل" submitColor="emerald" inputName="rotba_name" />
@endsection
