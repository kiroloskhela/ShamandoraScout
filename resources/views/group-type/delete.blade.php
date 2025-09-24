@extends('layouts.app')
@section('content')
    <x-form-card title="حذف نوع مجموعة كشفية" :action="route('group-type.destroy', $groupType->GroupTypeID)" method="DELETE"
        inputPlaceholder="ادخل اسم نوع المجموعة الكشفية" inputLabel="ادخل اسم نوع المجموعة الكشفية"
        submitText="حذف نوع مجموعة كشفية" submitColor="red" inputName="group_type_name" :inputValue="$groupType->GroupTypeName" />
@endsection
