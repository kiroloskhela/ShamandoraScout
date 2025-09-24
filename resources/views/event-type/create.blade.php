@extends('layouts.app', ['pageTitle' => 'إضافة نوع مناسبة' ?? ''])

@section('content')
    <x-form-card title="إضافة نوع مناسبة" :action="route('event-type.insert')" method="POST" inputPlaceholder="ادخل اسم نوع المناسبة"
        inputLabel="ادخل اسم نوع المناسبة" submitText="إضافة نوع المناسبة" submitColor="blue" inputName="event_type_name" />
@endsection
