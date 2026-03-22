@extends('layouts.app', ['pageTitle' => 'الألعاب'])

@section('content')
    <x-form-card title="مسح اسم الكلية" :action="route('games.destroy', $game->GameID)" method="DELETE" :inputValue="$game->Title" inputPlaceholder="ادخل اسم اللعبة"
        inputLabel="اسم اللعبة" submitText="مسح" submitColor="red" pageTitle="الألعاب" />
@endsection
