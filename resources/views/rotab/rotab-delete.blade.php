@extends('layouts.app' , ['pageTitle' => "مسح اسم الرتبة"])

@section('content')
<x-form-card title="مسح اسم الرتبة" :action="route('rotab.destroy', $rotab->RotbaID)" method="DELETE"
    :inputValue="$rotab->RotbaName" inputPlaceholder="ادخل اسم الرتبة" inputLabel="مسح اسم الرتبة" submitText="مسح"
    submitColor="red" inputName="rotba_name" />

@endsection