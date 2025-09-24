@extends('layouts.app', ['pageTitle' => 'فصائل الدم' ?? ''])

@section('content')
    <x-form-card title="مسح فصيله دم" :action="route('blood.destroy', $blood->BloodTypeID)" method="DELETE" :inputValue="$blood->BloodTypeName" inputPlaceholder="ادخل فصيله دم"
        inputLabel="مسح فصيله دم" submitText="مسح" submitColor="red" />
@endsection
