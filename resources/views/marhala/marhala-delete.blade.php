@extends('layouts.app', ['pageTitle' => 'مسح المرحلة الدراسية'])
@section('content')
    <x-form-card title="مسح مرحلة دراسية" :action="route('marhala.destroy', $marhala->MarhalaID)" method="DELETE" :inputValue="$marhala->MarhalaName"
        inputPlaceholder="ادخل مرحلة دراسية" inputLabel="مسح مرحلة دراسية" submitText="مسح" submitColor="red" />
@endsection
