@extends('layouts.app', ['pageTitle' => 'حذف شخص ' ?? ''])

@section('content')
    <x-form-card title="مسح شخص" :action="route('person.destroy', $person->PersonID)" method="DELETE" :inputValue="$person->ShamandoraCode" inputPlaceholder="ادخل اسم الشخص"
        inputLabel="مسح شخص" submitText="مسح" submitColor="red" />
@endsection
