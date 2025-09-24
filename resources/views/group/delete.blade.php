@extends('layouts.app', ['pageTitle' => 'حذف مجموعة' ?? ''])

@section('content')
    <x-form-card title="مسح اسم المجموعة" :action="route('group.destroy', $group->GroupID)" method="DELETE" :inputValue="$group->GroupName"
        inputPlaceholder="ادخل اسم المجموعة" inputLabel="تعديل اسم المجموعة" submitText="مسح" submitColor="red"
        pageTitle="المجموعات" />
@endsection
