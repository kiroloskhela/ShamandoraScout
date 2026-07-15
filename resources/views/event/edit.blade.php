@extends('layouts.app', ['pageTitle' => __('Edit event/occasion')])

@section('content')
    <div class="container-fluid">

        <div class="flex place-content-center mb-8">
            <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-4xl border-2 border-emerald-300" dir="rtl">
                <div class="mb-6 text-center">
                    <h2 class="text-xl font-bold text-gray-800" style="font-family: 'Cairo', sans-serif;">{{ __('Edit event/occasion') }}</h2>
                </div>

                @if ($errors->any())
                    <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-3 text-red-700 text-sm">
                        <ul class="list-disc pr-5 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form class="user" id="regForm" method="POST" action="{{ route('event.update', $event->EventID) }}">
                    @csrf
                    @method('PATCH')

                    <div class="space-y-6">

                        <!-- Season -->
                        <div class="relative">
                            <label for="season_id" class="block mb-2 text-sm font-medium text-slate-700"
                                style="font-family: 'Cairo', sans-serif; text-align: right;">{{ __('Season (optional)') }}</label>
                            <select name="season_id" id="season_id"
                                class="w-full h-12 px-4 text-sm border rounded-lg border-slate-200 text-slate-500 focus:border-emerald-500 focus:outline-none text-right"
                                style="font-family: 'Cairo', sans-serif; font-size: medium">
                                <option value="">{{ __('Not linked to a season') }}</option>
                                @foreach ($seasons as $season)
                                    <option value="{{ $season->SeasonID }}"
                                        @if (($selectedSeasonId ?? null) == $season->SeasonID) selected @endif>
                                        {{ $season->SeasonName }} - {{ $season->SeasonYear }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Event Type -->
                        <div class="relative">
                            <label for="event_type_id" class="block mb-2 text-sm font-medium text-slate-700"
                                style="font-family: 'Cairo', sans-serif; text-align: right;">{{ __('Scout event or occasion type') }}</label>
                            <select name="event_type_id" id="event_type_id" required
                                class="w-full h-12 px-4 text-sm border rounded-lg border-slate-200 text-slate-500 focus:border-emerald-500 focus:outline-none text-right"
                                style="font-family: 'Cairo', sans-serif; font-size: medium">
                                <option value="" disabled>{{ __('Choose scout event type') }}</option>
                                @foreach ($eventTypes as $eventType)
                                    <option value="{{ $eventType->EventTypeID }}"
                                        @if ($eventType->EventTypeID == $event->EventTypeID) selected @endif>
                                        {{ $eventType->EventTypeName }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Event Name -->
                        <div class="relative">
                            <input id="event_name" type="text" name="event_name"
                                value="{{ old('event_name', $event->EventName) }}"
                                placeholder="{{ __('Enter event or occasion name (optional)') }}"
                                class="relative w-full h-12 px-4 text-sm border rounded-lg outline-none border-slate-200 text-slate-500 focus:border-emerald-500 focus:outline-none text-right"
                                style="font-family: 'Cairo', sans-serif; font-size: medium" />
                            <label for="event_name"
                                class="cursor-text absolute right-2 -top-2 z-[1] px-2 text-xs text-slate-400 bg-white"
                                style="font-family: 'Cairo', sans-serif;">{{ __('Scout event or occasion name (optional)') }}</label>

                            <div id="autoNamePreview"
                                class="hidden mt-2 rounded-lg bg-emerald-50 border border-emerald-200 px-3 py-2 text-xs text-emerald-800"
                                style="font-family: 'Cairo', sans-serif;">
                            </div>
                        </div>

                        <!-- Qetaa -->
                        <div class="relative">
                            <label class="block mb-2 text-sm font-medium text-slate-700"
                                style="font-family: 'Cairo', sans-serif; text-align: right;">{{ __('Choose sectors linked to this event') }}</label>

                            <div class="border rounded-lg border-slate-200 p-4 bg-white min-h-32 max-h-48 overflow-y-auto">
                                @foreach ($qetaat as $qetaa)
                                    <label
                                        class="flex items-center mb-2 cursor-pointer hover:bg-emerald-50 p-2 rounded qetaa-option
                                        @if (in_array($qetaa->QetaaID, $selectedQetaat ?? [])) bg-emerald-50 border-l-4 border-emerald-500 @endif"
                                        style="font-family: 'Cairo', sans-serif; direction: rtl;">
                                        <input type="checkbox" name="qetaa_id[]" value="{{ $qetaa->QetaaID }}"
                                            data-qetaa-name="{{ $qetaa->QetaaName }}"
                                            @if (in_array($qetaa->QetaaID, $selectedQetaat ?? [])) checked @endif
                                            class="ml-2 w-4 h-4 text-emerald-500 bg-gray-100 border-gray-300 rounded focus:ring-emerald-500 focus:ring-2">
                                        <span class="text-sm text-slate-700" style="font-family: 'Cairo', sans-serif;">
                                            {{ $qetaa->QetaaName }}
                                        </span>
                                    </label>
                                @endforeach
                            </div>

                            <div id="qetaa-validation-error" class="hidden text-red-500 text-xs mt-1"
                                style="font-family: 'Cairo', sans-serif; text-align: right;">{{ __('Please choose at least one sector') }}</div>
                        </div>

                        <!-- Dates -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="relative">
                                <input id="event_start_date" type="date" name="event_start_date" required
                                    value="{{ old('event_start_date', $event->EventStartDate) }}"
                                    class="relative w-full h-12 px-4 text-sm transition-all border rounded-lg outline-none border-slate-200 text-slate-500 focus:border-emerald-500 focus:outline-none text-right"
                                    style="font-family: 'Cairo', sans-serif; font-size: medium" />
                                <label for="event_start_date"
                                    class="cursor-text absolute right-2 -top-2 z-[1] px-2 text-xs text-slate-400 bg-white"
                                    style="font-family: 'Cairo', sans-serif;">{{ __('Event start date') }}</label>
                            </div>

                            <div class="relative">
                                <input id="event_end_date" type="date" name="event_end_date"
                                    value="{{ old('event_end_date', $event->EventEndDate) }}"
                                    class="relative w-full h-12 px-4 text-sm transition-all border rounded-lg outline-none border-slate-200 text-slate-500 focus:border-emerald-500 focus:outline-none text-right"
                                    style="font-family: 'Cairo', sans-serif; font-size: medium" />
                                <label for="event_end_date"
                                    class="cursor-text absolute right-2 -top-2 z-[1] px-2 text-xs text-slate-400 bg-white"
                                    style="font-family: 'Cairo', sans-serif;">{{ __('Event end date') }}</label>
                            </div>
                        </div>

                        <!-- Submit -->
                        <div class="flex justify-center pt-6">
                            <button type="submit" id="submit-button"
                                class="inline-flex items-center justify-center h-12 gap-2 px-8 text-sm font-medium tracking-wide transition duration-300 rounded-full bg-emerald-50 text-emerald-600 hover:bg-emerald-100 hover:text-emerald-700"
                                style="font-family: 'Cairo', sans-serif; font-weight: bold;">{{ __('Update') }}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            $(document).ready(function() {
                const eventTypeSelect = document.getElementById('event_type_id');
                const eventNameInput = document.getElementById('event_name');
                const startDateInput = document.getElementById('event_start_date');
                const endDateInput = document.getElementById('event_end_date');
                const autoNamePreview = document.getElementById('autoNamePreview');

                function getSelectedEventTypeName() {
                    const option = eventTypeSelect.options[eventTypeSelect.selectedIndex];
                    if (!option || !option.value) return '';
                    return option.text.trim();
                }

                function getSelectedQetaaNames() {
                    return Array.from(document.querySelectorAll('input[name="qetaa_id[]"]:checked'))
                        .map(cb => cb.getAttribute('data-qetaa-name'))
                        .filter(Boolean);
                }

                function buildAutoName() {
                    const eventTypeName = getSelectedEventTypeName();
                    const qetaaNames = getSelectedQetaaNames();
                    const startDate = startDateInput.value;
                    const endDate = endDateInput.value || startDate;

                    const parts = [];
                    if (eventTypeName) parts.push(eventTypeName);
                    if (qetaaNames.length) parts.push(qetaaNames.join(' - '));
                    if (startDate && endDate) parts.push(startDate + ' ' + @json(__('to')) + ' ' + endDate);
                    else if (startDate) parts.push(startDate);

                    return parts.join(' - ').trim();
                }

                function refreshAutoNamePreview() {
                    const manualName = (eventNameInput.value || '').trim();
                    const generated = buildAutoName();

                    if (!manualName && generated) {
                        autoNamePreview.classList.remove('hidden');
                        autoNamePreview.innerHTML = @json(__('Suggested auto name:')) + ' <strong>' + generated + '</strong>';
                    } else {
                        autoNamePreview.classList.add('hidden');
                        autoNamePreview.innerHTML = '';
                    }
                }

                $('#event_type_id').select2({
                    theme: "classic",
                    placeholder: "{{ __('Choose scout event type') }}",
                    dir: "rtl"
                });

                $('#season_id').select2({
                    theme: "classic",
                    placeholder: "{{ __('Choose season') }}",
                    dir: "rtl",
                    allowClear: true
                });

                $('#regForm').on('submit', function(e) {
                    const eventType = $('#event_type_id').val();
                    const qetaaCheckboxes = $('input[name="qetaa_id[]"]:checked');
                    const startDate = $('#event_start_date').val();
                    let endDate = $('#event_end_date').val();

                    $('#qetaa-validation-error').addClass('hidden');

                    if (!eventType || qetaaCheckboxes.length === 0 || !startDate) {
                        e.preventDefault();

                        if (qetaaCheckboxes.length === 0) {
                            $('#qetaa-validation-error').removeClass('hidden');
                        }

                        alert(@json(__('Please fill all required fields')));
                        return false;
                    }

                    if (!endDate) {
                        const confirmSameDay = confirm(
                            @json(__('End date not selected. Do you want to set it to the same as the start date?')));
                        if (!confirmSameDay) {
                            $('#event_end_date').focus();
                            e.preventDefault();
                            return false;
                        }

                        $('#event_end_date').val(startDate);
                        endDate = startDate;
                    }

                    if (new Date(startDate) > new Date(endDate)) {
                        e.preventDefault();
                        alert(@json(__('Event start date must be before the end date')));
                        return false;
                    }

                    const manualName = ($('#event_name').val() || '').trim();
                    if (!manualName) {
                        const generated = buildAutoName();

                        if (!generated) {
                            e.preventDefault();
                            alert(@json(__('Cannot auto-generate event name before choosing event type, sector, and date')));
                            return false;
                        }

                        $('#event_name').val(generated);
                    }
                });

                $('input[name="qetaa_id[]"]').on('change', function() {
                    const label = $(this).parent();

                    if ($(this).is(':checked')) {
                        label.addClass('bg-emerald-50 border-l-4 border-emerald-500');
                    } else {
                        label.removeClass('bg-emerald-50 border-l-4 border-emerald-500');
                    }

                    if ($('input[name="qetaa_id[]"]:checked').length > 0) {
                        $('#qetaa-validation-error').addClass('hidden');
                    }

                    refreshAutoNamePreview();
                });

                $('#event_type_id, #event_start_date, #event_end_date').on('change', function() {
                    refreshAutoNamePreview();
                });

                $('#event_name').on('input', function() {
                    refreshAutoNamePreview();
                });

                refreshAutoNamePreview();
            });
        </script>
    @endpush
@endsection
