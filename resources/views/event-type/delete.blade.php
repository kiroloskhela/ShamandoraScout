@extends('layouts.app', ['pageTitle' => 'مسح نوع المناسبة' ?? ''])

@section('content')
    <x-form-card title="مسح نوع المناسبة" :action="route('event-type.destroy', $eventType->EventTypeID)" method="DELETE" :inputValue="$eventType->EventTypeName"
        inputPlaceholder="ادخل اسم نوع المناسبة" inputLabel="مسح اسم نوع المناسبة" submitText="مسح" submitColor="red"
        pageTitle="المناسبات" />
@endsection
