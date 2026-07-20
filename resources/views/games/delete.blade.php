@extends('layouts.app', ['pageTitle' => __('Games')])

@section('content')
    <x-form-card :title="__('Delete game')" :action="route('games.destroy', $game->GameID)" method="DELETE" :inputValue="$game->Title" inputPlaceholder="{{ __('Form label 40e9471b') }}"
        inputLabel="{{ __('Game name') }}" submitText="{{ __('Delete') }}" submitColor="red" pageTitle="__('Manage games')" />
@endsection
