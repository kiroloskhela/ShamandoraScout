@extends('layouts.app', ['pageTitle' => __('Review place booking request')])

@section('content')
    <div class="container mx-auto px-4 py-8">

        <div class="mb-6 text-center">
            <h1 class="text-2xl font-bold text-gray-800 mb-2">{{ __('Review place booking request #:id', ['id' => $booking->BookingID]) }}</h1>
            <p class="text-gray-600">
                {{ $booking->BookingDate }} • {{ __('From :from to :to', ['from' => $booking->TimeFrom, 'to' => $booking->TimeTo]) }}
            </p>
        </div>

        {{-- Alerts --}}
        @if ($errors->any())
            <div class="mb-6 p-3 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm">
                <ul class="list-disc pr-5 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li class="leading-5">{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        @if (session('success'))
            <div class="mb-6 p-3 rounded-lg bg-green-50 border border-green-200 text-green-700 text-sm">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="mb-6 p-3 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm">
                {{ session('error') }}
            </div>
        @endif

        {{-- Top Card --}}
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6 border-2 border-slate-200">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div class="text-sm text-gray-700 flex items-center gap-2">
                    <span class="font-bold">{{ __('Status:') }}</span>
                    @if ($booking->Status === 'pending')
                        <span
                            class="px-3 py-1 rounded-full text-xs bg-yellow-50 text-yellow-700 border border-yellow-200">{{ __('Pending review') }}</span>
                    @elseif ($booking->Status === 'approved')
                        <span class="px-3 py-1 rounded-full text-xs bg-green-50 text-green-700 border border-green-200">{{ __('Approved') }}</span>
                    @else
                        <span
                            class="px-3 py-1 rounded-full text-xs bg-red-50 text-red-700 border border-red-200">{{ __('Rejected') }}</span>
                    @endif
                </div>

                <a href="{{ route('admin.place_bookings.index') }}"
                    class="px-4 py-2 text-xs rounded-lg bg-gray-50 text-gray-700 hover:bg-gray-100 transition border border-gray-200">
                    {{ __('Back to list') }}
                </a>
            </div>

            {{-- Info Badges --}}
            <div class="mt-4 flex flex-wrap gap-2">
                <span class="px-3 py-1 rounded-full text-xs bg-blue-50 text-blue-700 border border-blue-200">
                    {{ __('Requester:') }} {{ $booking->UserName ?? '—' }}
                </span>

                <span class="px-3 py-1 rounded-full text-xs bg-slate-50 text-slate-700 border border-slate-200">
                    {{ __('Sector:') }} {{ $booking->QetaaName ?? '—' }}
                </span>

                <span class="px-3 py-1 rounded-full text-xs bg-purple-50 text-purple-700 border border-purple-200">
                    {{ __('Location:') }} {{ $booking->LocationName ?? '—' }}
                </span>

                <span class="px-3 py-1 rounded-full text-xs bg-indigo-50 text-indigo-700 border border-indigo-200">
                    {{ __('Place:') }} {{ $booking->PlaceName ?? '—' }}
                </span>

                <span class="px-3 py-1 rounded-full text-xs bg-gray-50 text-gray-700 border border-gray-200">
                    {{ __('Reviewer:') }} {{ $booking->ReviewerName ?? '—' }}
                </span>
            </div>

            @if (!empty($booking->UserNote))
                <div class="mt-5 p-3 rounded-lg bg-slate-50 border border-slate-200 text-sm text-slate-700">
                    <div class="font-bold mb-1">{{ __('Requester note:') }}</div>
                    <div class="leading-6">{{ $booking->UserNote }}</div>
                </div>
            @endif

            @if (!empty($booking->AdminNote))
                <div
                    class="mt-4 p-3 rounded-lg bg-blue-50 border border-blue-200 text-sm text-blue-800 whitespace-pre-line">
                    <div class="font-bold mb-1">{{ __('Admin note:') }}</div>
                    {{ $booking->AdminNote }}
                </div>
            @endif
        </div>

        {{-- Approve with optional edit --}}
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6 border-2 border-green-200">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-bold text-gray-800">{{ __('Approve request (with optional edits)') }}</h2>
                <span class="text-xs text-gray-500">{{ __('You can approve as-is or modify') }}</span>
            </div>

            @if ($booking->Status === 'pending')
                <form method="POST" action="{{ route('admin.place_bookings.approve', $booking->BookingID) }}">
                    @csrf

                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <label class="block mb-2 text-sm text-gray-700">{{ __('Approved place') }}</label>
                            <select name="approved_place_id" id="approved_place_id"
                                class="w-full h-12 border rounded-lg px-4 text-right border-slate-200 text-slate-700 focus:border-green-500 focus:outline-none"
                                required>
                                <option value="" disabled>{{ __('Choose place') }}</option>

                                @foreach ($places as $p)
                                    <option value="{{ $p->PlaceID }}"
                                        {{ (string) old('approved_place_id', $booking->ApprovedPlaceID ?? $booking->PlaceID) === (string) $p->PlaceID ? 'selected' : '' }}>
                                        {{ $p->PlaceName }} -- {{ $p->LocationName }}
                                    </option>
                                @endforeach
                            </select>

                            <p class="mt-2 text-xs text-gray-500">{{ __('Default is the same place from the request.') }}</p>
                        </div>

                        <div>
                            <label class="block mb-2 text-sm text-gray-700">{{ __('Date') }}</label>
                            <input type="date" name="approved_booking_date" id="approved_booking_date"
                                value="{{ old('approved_booking_date', $booking->BookingDate) }}"
                                class="w-full h-12 border rounded-lg px-4 text-right border-slate-200 text-slate-700 focus:border-green-500 focus:outline-none"
                                required>
                            <p class="mt-2 text-xs text-gray-500">{{ __('Default is the same date from the request.') }}</p>
                        </div>
                    </div>

                    <div class="grid md:grid-cols-2 gap-6 mt-6">
                        <div>
                            <label class="block mb-2 text-sm text-gray-700">{{ __('From') }}</label>
                            <input type="time" name="approved_time_from" id="approved_time_from"
                                value="{{ old('approved_time_from', $booking->TimeFrom) }}"
                                class="w-full h-12 border rounded-lg px-4 text-right border-slate-200 text-slate-700 focus:border-green-500 focus:outline-none"
                                required>
                        </div>

                        <div>
                            <label class="block mb-2 text-sm text-gray-700">{{ __('To') }}</label>
                            <input type="time" name="approved_time_to" id="approved_time_to"
                                value="{{ old('approved_time_to', $booking->TimeTo) }}"
                                class="w-full h-12 border rounded-lg px-4 text-right border-slate-200 text-slate-700 focus:border-green-500 focus:outline-none"
                                required>
                        </div>
                    </div>

                    <div class="mt-6">
                        <label class="block mb-2 text-sm text-gray-700">{{ __('General note for user (optional)') }}</label>
                        <textarea name="admin_note" rows="3"
                            class="w-full border rounded-lg p-3 text-right border-slate-200 text-slate-700 focus:border-green-500 focus:outline-none"
                            placeholder="{{ __('e.g. Place changed to another hall...') }}">{{ old('admin_note') }}</textarea>
                    </div>

                    <div class="mt-6 flex justify-center gap-3">
                        <button type="submit"
                            class="inline-flex items-center justify-center h-12 px-10 text-sm font-medium rounded-full
                                   bg-green-50 text-green-700 hover:bg-green-100 transition border border-green-200">
                            {{ __('Approve request') }}
                        </button>
                    </div>
                </form>
            @else
                {{-- Read-only after review --}}
                <div class="p-4 rounded-lg bg-slate-50 border border-slate-200 text-sm text-slate-700">
                    <div class="font-bold mb-2">{{ __('Request already reviewed') }}</div>

                    @if ($booking->Status === 'approved')
                        <div class="grid md:grid-cols-3 gap-3">
                            <div class="p-3 rounded-lg bg-white border border-slate-200">
                                <div class="text-xs text-slate-500 mb-1">{{ __('Approved place') }}</div>
                                <div class="font-semibold text-slate-900">
                                    {{ $booking->ApprovedPlaceName ?? ($booking->PlaceName ?? '—') }}</div>
                            </div>

                            <div class="p-3 rounded-lg bg-white border border-slate-200">
                                <div class="text-xs text-slate-500 mb-1">{{ __('From') }}</div>
                                <div class="font-semibold text-slate-900">
                                    {{ $booking->ApprovedTimeFrom ?? ($booking->TimeFrom ?? '—') }}</div>
                            </div>

                            <div class="p-3 rounded-lg bg-white border border-slate-200">
                                <div class="text-xs text-slate-500 mb-1">{{ __('To') }}</div>
                                <div class="font-semibold text-slate-900">
                                    {{ $booking->ApprovedTimeTo ?? ($booking->TimeTo ?? '—') }}</div>
                            </div>
                        </div>
                    @endif

                    @if (!empty($booking->AdminNote))
                        <div
                            class="mt-4 p-3 rounded-lg bg-blue-50 border border-blue-200 text-sm text-blue-800 whitespace-pre-line">
                            <div class="font-bold mb-1">{{ __('Admin note:') }}</div>
                            {{ $booking->AdminNote }}
                        </div>
                    @endif
                </div>
            @endif
        </div>

        {{-- Reject (only pending) --}}
        @if ($booking->Status === 'pending')
            <div class="bg-white rounded-lg shadow-lg p-6 border-2 border-red-200">
                <h2 class="text-lg font-bold text-gray-800 mb-4">{{ __('Reject request') }}</h2>

                <form method="POST" action="{{ route('admin.place_bookings.reject', $booking->BookingID) }}">
                    @csrf

                    <label class="block mb-2 text-sm text-gray-700">{{ __('Rejection reason (optional)') }}</label>
                    <textarea name="admin_note" rows="2"
                        class="w-full border rounded-lg p-3 text-right border-slate-200 text-slate-700 focus:border-red-500 focus:outline-none"
                        placeholder="{{ __('e.g. No place available...') }}">{{ old('admin_note') }}</textarea>

                    <div class="mt-4 flex justify-center">
                        <button type="submit"
                            class="inline-flex items-center justify-center h-12 px-10 text-sm font-medium rounded-full
                                   bg-red-50 text-red-700 hover:bg-red-100 transition border border-red-200">{{ __('Reject') }}</button>
                    </div>
                </form>
            </div>
        @endif

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const from = document.getElementById('approved_time_from');
            const to = document.getElementById('approved_time_to');
            const form = document.querySelector('form[action*="approve"]');

            if (form) {
                form.addEventListener('submit', function(e) {
                    if (from && to && from.value && to.value && from.value >= to.value) {
                        e.preventDefault();
                        alert(@json(__('End time must be after start time.')));
                    }
                });
            }
        });
    </script>
@endsection
