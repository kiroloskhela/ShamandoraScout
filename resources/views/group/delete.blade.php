@extends('layouts.app', ['pageTitle' => __('Delete group')])

@section('content')
    <x-form-card title="{{ __('Delete group name') }}" :action="route('group.destroy', $group->GroupID)" method="DELETE" :inputValue="$group->GroupName"
        inputPlaceholder="{{ __('Form label 6d0a0bd6') }}" inputLabel="{{ __('Form label 5845812a') }}" submitText="{{ __('Delete') }}" submitColor="red"
        pageTitle="__('Groups')" />
@endsection
