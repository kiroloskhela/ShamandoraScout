@extends('layouts.app', ['pageTitle' => 'مسح المناسبة'])

@section('content')
    <x-form-card title="مسح المناسبة" :action="route('event.destroy', $event->EventID)" method="DELETE" :inputValue="$event->EventName" inputPlaceholder="ادخل اسم المناسبة"
        inputLabel="مسح اسم المناسبة" submitText="مسح" submitColor="red" pageTitle="المناسبات" inputName="event_name" />
@endsection
