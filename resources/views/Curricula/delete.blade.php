@extends('layouts.app', ['pageTitle' => 'مسح المحاضرة'])

@section('content')
    <x-form-card title="مسح المحاضرة" :action="route('curricula.destroy', $curriculum->CurriculaID)" method="DELETE" :inputValue="$curriculum->CurriculaName" inputPlaceholder="ادخل اسم المحاضرة"
        inputLabel="اسم المحاضرة" submitText="مسح" submitColor="red" inputName="curricula_name" />
@endsection
