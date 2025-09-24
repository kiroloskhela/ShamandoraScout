@extends('layouts.app')

@section('content')
    <x-form-card title="تعديل نوع مجموعة كشفية" :action="route('group-type.update', $groupType->GroupTypeID)" method="PATCH"
        inputPlaceholder="ادخل اسم نوع المجموعة الكشفية" inputLabel="ادخل اسم نوع المجموعة الكشفية"
        submitText="تحديث نوع مجموعة كشفية" submitColor="green" inputName="group_type_name" :inputValue="$groupType->GroupTypeName" />
@endsection
