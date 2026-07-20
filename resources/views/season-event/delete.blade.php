@extends('layouts.app' , ['pageTitle' => __('Delete season-event link')])

@section('content')
<div class="flex place-content-center">
    <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-md border-2 border-red-300">
        <div class="mb-6 text-center">
            <h2 class="text-xl font-bold text-gray-800">تأكيد الحذف</h2>
        </div>

        <p class="mb-4 text-center text-gray-600">
            هل أنت متأكد أنك تريد حذف الربط التالي؟
        </p>

        <ul class="mb-6 text-gray-700 text-right">
            <li><strong>{{ __('Season:') }}</strong> {{ $season->SeasonName }} ({{ $season->SeasonYear }})</li>
            <li><strong>{{ __('Event:') }}</strong> {{ $event->EventName }} 
                ({{ \Carbon\Carbon::parse($event->EventStartDate)->format('Y-m-d') }} → 
                 {{ \Carbon\Carbon::parse($event->EventEndDate)->format('Y-m-d') }})
            </li>
        </ul>

        <form method="POST" action="{{ route('season-event.destroy', $seasonEvent->SeasonEventID) }}" class="flex justify-center space-x-4 space-x-reverse">
            @csrf
            @method('DELETE')
            <button type="submit"
                class="inline-flex items-center justify-center h-12 px-6 text-sm font-medium rounded-full bg-red-50 text-red-600 hover:bg-red-100 hover:text-red-700 transition">
                نعم، احذف
            </button>
            <a href="{{ route('season-event.index') }}"
                class="inline-flex items-center justify-center h-12 px-6 text-sm font-medium rounded-full bg-gray-50 text-gray-600 hover:bg-gray-100 hover:text-gray-700 transition">{{ __('Cancel') }}</a>
        </form>
    </div>
</div>
@endsection
