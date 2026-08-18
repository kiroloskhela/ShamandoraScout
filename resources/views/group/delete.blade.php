@extends('layouts.app', ['pageTitle' => __('Delete group')])

@section('content')
    <x-form-card title="{{ __('Delete group name') }}" :action="route('group.destroy', $group->GroupID)" method="DELETE" :inputValue="$group->GroupName"
        inputPlaceholder="{{ __('Enter group name') }}" inputLabel="{{ __('Group name') }}" submitText="{{ __('Delete') }}" submitColor="red"
        pageTitle="__('Groups')" />
@endsection
