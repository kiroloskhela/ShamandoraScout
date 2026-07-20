@extends('layouts.app', ['pageTitle' => __('Delete finance plan')])

@section('content')
    <div class="flex place-content-center">
        <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-md border-2 border-red-300">
            <div class="mb-6 text-center">
                <h2 class="text-xl font-bold text-red-700">حذف الخطة المالية</h2>
            </div>

            @if ($errors->has('general'))
                <div class="mb-4 rounded-lg bg-red-100 border border-red-300 text-red-800 px-4 py-3">
                    {{ $errors->first('general') }}
                </div>
            @endif

            <div class="space-y-3 text-sm text-gray-700 mb-6">
                <p><strong>{{ __('Season:') }}</strong> {{ $finance->SeasonName }} ({{ $finance->SeasonYear }})</p>
                <p><strong>{{ __('Event:') }}</strong> {{ $finance->EventName }}</p>
                <p><strong>{{ __('Event start:') }}</strong> {{ $finance->EventStartDate }}</p>
                <p><strong>{{ __('Event end:') }}</strong> {{ $finance->EventEndDate }}</p>
            </div>

            <div class="mb-6 text-red-600 font-medium text-center">
                هل أنت متأكد من حذف هذه الخطة المالية؟
            </div>

            <form method="POST" action="{{ route('finance.destroy', $finance->SeasonEventID) }}">
                @csrf
                <div class="flex justify-center gap-3">
                    <a href="{{ route('finance.index') }}"
                        class="inline-flex items-center justify-center h-12 px-6 text-sm font-medium rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 transition">{{ __('Back') }}</a>

                    <button type="submit"
                        class="inline-flex items-center justify-center h-12 px-6 text-sm font-medium rounded-lg bg-red-600 text-white hover:bg-red-700 transition">
                        تأكيد الحذف
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
