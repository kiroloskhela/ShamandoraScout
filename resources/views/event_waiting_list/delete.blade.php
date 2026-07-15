@extends('layouts.app', ['pageTitle' => 'حذف من قائمة الانتظار'])

@section('content')
    <div class="flex place-content-center" dir="rtl">
        <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-2xl border-2 border-red-300">
            <div class="mb-6 text-center">
                <h2 class="text-xl font-bold text-red-700">تأكيد حذف من قائمة الانتظار</h2>
            </div>

            @if ($errors->has('general'))
                <div class="mb-4 rounded-lg bg-red-100 border border-red-300 text-red-800 px-4 py-3">
                    {{ $errors->first('general') }}
                </div>
            @endif

            <div class="mb-6 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-900">
                هل أنت متأكد أنك تريد حذف هذا الشخص من قائمة الانتظار؟
            </div>

            <div class="mb-6 rounded-lg border border-slate-200 bg-slate-50 p-6 space-y-3 text-sm text-gray-800">
                <div><strong>{{ __('Season:') }}</strong> {{ $row->SeasonName }} ({{ $row->SeasonYear }})</div>
                <div><strong>{{ __('Event:') }}</strong> {{ $row->EventTypeName }} - {{ $row->EventName }}</div>
                <div><strong>{{ __('Event start:') }}</strong> {{ $row->EventStartDate }}</div>
                <div><strong>{{ __('Event end:') }}</strong> {{ $row->EventEndDate }}</div>
                <hr>
                <div><strong>{{ __('Name:') }}</strong> {{ $row->PersonFullName }}</div>
                <div><strong>PersonID:</strong> {{ $row->PersonID }}</div>
                <div><strong>{{ __('Mobile:') }}</strong> {{ $row->PersonPersonalMobileNumber ?? '-' }}</div>
                <div><strong>القطاع:</strong> {{ $row->QetaaName ?? '-' }}</div>
                <div><strong>أضافه الخادم:</strong> {{ $row->ServentFullName ?? '-' }}</div>
                <div><strong>تاريخ الإضافة:</strong> {{ $row->CreatedAt }}</div>
            </div>

            <div class="flex justify-center gap-3">
                <a href="{{ route('eventWaitingList.index', $row->SeasonEventID) }}"
                    class="inline-flex items-center justify-center h-12 px-8 text-sm font-medium rounded-full bg-gray-100 text-gray-700 hover:bg-gray-200 transition">{{ __('Back') }}</a>

                <form method="POST" action="{{ route('eventWaitingList.destroy', $row->SeasonEventWaitingListID) }}">
                    @csrf
                    @method('DELETE')

                    <button type="submit"
                        class="inline-flex items-center justify-center h-12 px-8 text-sm font-medium rounded-full bg-red-100 text-red-700 hover:bg-red-200 transition">
                        تأكيد الحذف
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection
