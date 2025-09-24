@extends('layouts.app', ['pageTitle' => 'تعديل نوع مناسبة' ?? ''])
@section('content')
    <x-form-card title="تعديل نوع مناسبة" :action="route('event-type.update', $eventType->EventTypeID)" method="PATCH" :inputValue="$eventType->EventTypeName"
        inputPlaceholder="ادخل اسم نوع المناسبة" inputLabel="تعديل اسم نوع المناسبة" submitText="تعديل" submitColor="emerald"
        pageTitle="المناسبات" inputName="event_type_name" />
@endsection
