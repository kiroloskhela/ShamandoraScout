@extends('layouts.app', ['pageTitle' => 'إضافة حدث/مناسبة'])

@section('content')
    <div class="container-fluid">
        <div class="flex place-content-center mb-8">
            <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-4xl border-2 border-blue-300" dir="rtl">
                <!-- Title -->
                <div class="mb-6 text-center">
                    <h2 class="text-xl font-bold text-gray-800" style="font-family: 'Cairo', sans-serif;">
                        اضافة حدث/مناسبة جديدة
                    </h2>
                </div>

                <!-- Errors -->
                @if ($errors->any())
                    <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-3 text-red-700 text-sm">
                        <ul class="list-disc pr-5 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form class="user" id="regForm" method="POST" action="{{ route('event.insert') }}">
                    @csrf
                    <div class="space-y-6">

                        <!-- Season -->
                        <div>
                            <label for="season_id" class="block mb-2 text-sm font-medium text-slate-700"
                                style="font-family: 'Cairo', sans-serif; text-align: right;">
                                الموسم (اختياري)
                            </label>
                            <select name="season_id" id="season_id"
                                class="w-full h-12 px-4 text-sm border rounded-lg border-slate-200 text-slate-600 focus:border-blue-500 focus:outline-none text-right"
                                style="font-family: 'Cairo', sans-serif; font-size: medium">
                                <option value="">بدون ربط بموسم</option>
                                @isset($seasons)
                                    @foreach ($seasons as $season)
                                        <option value="{{ $season->SeasonID }}">
                                            {{ $season->SeasonName }} - {{ $season->SeasonYear }}
                                        </option>
                                    @endforeach
                                @endisset
                            </select>
                        </div>

                        <!-- Event Type -->
                        <div>
                            <label for="event_type_id" class="block mb-2 text-sm font-medium text-slate-700"
                                style="font-family: 'Cairo', sans-serif; text-align: right;">نوع الحدث أو المناسبة
                                الكشفية</label>
                            <select name="event_type_id" id="event_type_id" required
                                class="w-full h-12 px-4 text-sm border rounded-lg border-slate-200 text-slate-600 focus:border-blue-500 focus:outline-none text-right"
                                style="font-family: 'Cairo', sans-serif; font-size: medium">
                                <option value="" disabled selected>اختر نوع الحدث أو المناسبة الكشفية</option>
                                @foreach ($eventTypes as $eventType)
                                    <option value="{{ $eventType->EventTypeID }}">{{ $eventType->EventTypeName }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Event Name -->
                        <div class="relative">
                            <input id="event_name" type="text" name="event_name"
                                placeholder="ادخل اسم الحدث أو المناسبة (اختياري - سيتم تكوينه تلقائياً إذا تركته فارغاً)"
                                class="w-full h-12 px-4 text-sm border rounded-lg border-slate-200 text-slate-600 focus:border-blue-500 focus:outline-none text-right"
                                style="font-family: 'Cairo', sans-serif; font-size: medium" />
                            <label for="event_name" class="sr-only">اسم الحدث</label>
                            <p class="mt-2 text-xs text-slate-500" style="font-family: 'Cairo', sans-serif;">
                                في حالة ترك الاسم فارغًا سيتم تكوينه تلقائيًا من:
                                نوع الحدث + القطاع + تاريخ البداية + تاريخ النهاية
                            </p>
                            <div id="autoNamePreview"
                                class="hidden mt-2 rounded-lg bg-blue-50 border border-blue-200 px-3 py-2 text-xs text-blue-800"
                                style="font-family: 'Cairo', sans-serif;">
                            </div>
                        </div>

                        <!-- Qetaa Multi-Selection -->
                        <div>
                            <label class="block mb-2 text-sm font-medium text-slate-700"
                                style="font-family: 'Cairo', sans-serif; text-align: right;">اختر القطاعات المربوطة بهذا
                                الحدث</label>
                            <div class="border rounded-lg border-slate-200 p-4 bg-white min-h-32 max-h-48 overflow-y-auto">
                                @foreach ($qetaat as $qetaa)
                                    <label
                                        class="flex items-center mb-2 cursor-pointer hover:bg-slate-50 p-2 rounded qetaa-option"
                                        style="font-family: 'Cairo', sans-serif; direction: rtl;">
                                        <input type="checkbox" name="qetaa_id[]" value="{{ $qetaa->QetaaID }}"
                                            data-qetaa-name="{{ $qetaa->QetaaName }}"
                                            class="ml-2 w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2">
                                        <span class="text-sm text-slate-700">{{ $qetaa->QetaaName }}</span>
                                    </label>
                                @endforeach
                            </div>
                            <div id="qetaa-validation-error" class="hidden text-red-500 text-xs mt-1"
                                style="font-family: 'Cairo', sans-serif; text-align: right;">
                                يرجى اختيار قطاع واحد على الأقل
                            </div>
                        </div>

                        <!-- Recurrence Toggle -->
                        <div class="flex items-center gap-3">
                            <input id="is_recursive" type="checkbox" name="is_recursive"
                                class="h-4 w-4 border-slate-300 rounded text-blue-600 focus:ring-blue-500">
                            <label for="is_recursive" class="text-sm text-gray-700">متكرر (اختيار أيام متعددة
                                منفصلة)</label>
                        </div>

                        <!-- Date Range (single event) -->
                        <div id="singleRangeWrap" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="event_start_date" class="block mb-2 text-sm text-gray-700">تاريخ بداية
                                    الحدث</label>
                                <input id="event_start_date" type="date" name="event_start_date"
                                    class="w-full h-12 px-4 text-sm border rounded-lg border-slate-200 text-slate-600 focus:border-blue-500 focus:outline-none text-right">
                            </div>
                            <div>
                                <label for="event_end_date" class="block mb-2 text-sm text-gray-700">تاريخ نهاية
                                    الحدث</label>
                                <input id="event_end_date" type="date" name="event_end_date"
                                    class="w-full h-12 px-4 text-sm border rounded-lg border-slate-200 text-slate-600 focus:border-blue-500 focus:outline-none text-right">
                            </div>
                        </div>

                        <!-- Multiple Days (recurring events) -->
                        <div id="multiDatesWrap" class="hidden rounded-lg border border-slate-200 p-4">
                            <div class="flex items-center justify-between">
                                <p class="text-sm text-slate-700">الأيام المختارة (لكل يوم سيتم إنشاء حدث منفصل يبدأ وينتهي
                                    في نفس اليوم)</p>
                                <button type="button" id="addDateBtn"
                                    class="h-10 px-4 rounded-full bg-blue-50 text-blue-700 hover:bg-blue-100 text-sm">
                                    + إضافة يوم
                                </button>
                            </div>

                            <div id="datesList" class="mt-3 space-y-3"></div>

                            <div id="multiDatesError" class="hidden text-red-600 text-xs mt-2">
                                يرجى إضافة يوم واحد على الأقل عند اختيار "متكرر"
                            </div>
                        </div>

                        <!-- Submit -->
                        <div class="flex justify-center pt-6">
                            <button type="submit" id="submit-button"
                                class="inline-flex items-center justify-center h-12 gap-2 px-8 text-sm font-bold rounded-full bg-blue-600 text-white hover:bg-blue-700 transition"
                                style="font-family: 'Cairo', sans-serif;">
                                ادخال
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            (function() {
                const isRecursive = document.getElementById('is_recursive');
                const singleWrap = document.getElementById('singleRangeWrap');
                const multiWrap = document.getElementById('multiDatesWrap');
                const addDateBtn = document.getElementById('addDateBtn');
                const datesList = document.getElementById('datesList');
                const multiErr = document.getElementById('multiDatesError');
                const qetaaErr = document.getElementById('qetaa-validation-error');
                const form = document.getElementById('regForm');

                const eventTypeSelect = document.getElementById('event_type_id');
                const eventNameInput = document.getElementById('event_name');
                const startDateInput = document.getElementById('event_start_date');
                const endDateInput = document.getElementById('event_end_date');
                const autoNamePreview = document.getElementById('autoNamePreview');

                function formatDateForName(dateStr) {
                    if (!dateStr) return '';
                    return dateStr;
                }

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

                    if (startDate && endDate) {
                        parts.push(formatDateForName(startDate) + ' إلى ' + formatDateForName(endDate));
                    } else if (startDate) {
                        parts.push(formatDateForName(startDate));
                    }

                    return parts.join(' - ').trim();
                }

                function refreshAutoNamePreview() {
                    const manualName = (eventNameInput.value || '').trim();
                    const generated = buildAutoName();

                    if (!manualName && generated) {
                        autoNamePreview.classList.remove('hidden');
                        autoNamePreview.innerHTML = 'الاسم التلقائي المقترح: <strong>' + generated + '</strong>';
                    } else {
                        autoNamePreview.classList.add('hidden');
                        autoNamePreview.innerHTML = '';
                    }
                }

                function getAllDateInputs() {
                    return Array.from(datesList.querySelectorAll('input[name="event_multi_dates[]"]'));
                }

                function findDuplicate(value, current) {
                    if (!value) return false;
                    return getAllDateInputs().some(inp => inp !== current && inp.value === value);
                }

                function showDuplicateError(msg = 'هناك أيام مكررة. برجاء اختيار كل يوم مرة واحدة فقط.') {
                    multiErr.textContent = msg;
                    multiErr.classList.remove('hidden');
                }

                function hideDuplicateError() {
                    multiErr.classList.add('hidden');
                }

                function validateUniqueDatesOnChange(input) {
                    if (findDuplicate(input.value, input)) {
                        showDuplicateError('هذا اليوم مكرر بالفعل. اختر يومًا مختلفًا.');
                        input.value = '';
                        input.focus();
                        return false;
                    }
                    const hasDup = hasAnyDuplicates();
                    if (!hasDup) hideDuplicateError();
                    return true;
                }

                function hasAnyDuplicates() {
                    const vals = getAllDateInputs().map(i => i.value).filter(Boolean);
                    const uniq = new Set(vals);
                    return uniq.size !== vals.length;
                }

                function refreshMode() {
                    const on = isRecursive.checked;
                    singleWrap.classList.toggle('hidden', on);
                    multiWrap.classList.toggle('hidden', !on);

                    startDateInput.required = !on;
                    endDateInput.required = false;

                    if (on && datesList.children.length === 0) addRow();
                    refreshAutoNamePreview();
                }

                function addRow(val = '') {
                    const row = document.createElement('div');
                    row.className = 'flex items-center gap-3';
                    row.innerHTML = `
                        <input type="date" name="event_multi_dates[]" value="${val}"
                            class="w-full h-12 px-4 text-sm border rounded-lg border-slate-200 text-slate-600 focus:border-blue-500 focus:outline-none text-right"
                            required>
                        <button type="button"
                                class="h-10 px-3 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 text-sm remove-date">
                            إزالة
                        </button>
                    `;
                    datesList.appendChild(row);

                    const input = row.querySelector('input[type="date"]');
                    input.addEventListener('change', () => validateUniqueDatesOnChange(input));

                    hookRemove(row.querySelector('.remove-date'));
                }

                function hookRemove(btn) {
                    btn.addEventListener('click', function() {
                        const rows = datesList.querySelectorAll('div.flex.items-center.gap-3');
                        if (rows.length <= 1) {
                            const input = rows[0].querySelector('input[type="date"]');
                            input.value = '';
                        } else {
                            btn.closest('div.flex.items-center.gap-3').remove();
                        }

                        if (!hasAnyDuplicates()) hideDuplicateError();
                    });
                }

                async function handleMissingEndDate() {
                    if (isRecursive.checked) return true;

                    const startDate = startDateInput.value;
                    const endDate = endDateInput.value;

                    if (!startDate) {
                        alert('يرجى إدخال تاريخ البداية');
                        startDateInput.focus();
                        return false;
                    }

                    if (!endDate) {
                        const confirmSameDay = confirm('لم يتم اختيار تاريخ النهاية. هل تريد جعله نفس تاريخ البداية؟');
                        if (!confirmSameDay) {
                            endDateInput.focus();
                            return false;
                        }
                        endDateInput.value = startDate;
                    }

                    if (new Date(startDateInput.value) > new Date(endDateInput.value)) {
                        alert('تاريخ بداية الحدث يجب أن يكون قبل تاريخ النهاية');
                        return false;
                    }

                    return true;
                }

                function fillAutomaticNameIfNeeded() {
                    const manualName = (eventNameInput.value || '').trim();
                    if (manualName) return true;

                    const generated = buildAutoName();

                    if (!generated) {
                        alert('لا يمكن تكوين اسم الحدث تلقائياً قبل اختيار نوع الحدث والقطاع والتاريخ');
                        return false;
                    }

                    eventNameInput.value = generated;
                    return true;
                }

                isRecursive.addEventListener('change', refreshMode);
                if (addDateBtn) addDateBtn.addEventListener('click', () => addRow(''));

                eventTypeSelect.addEventListener('change', refreshAutoNamePreview);
                eventNameInput.addEventListener('input', refreshAutoNamePreview);
                startDateInput.addEventListener('change', refreshAutoNamePreview);
                endDateInput.addEventListener('change', refreshAutoNamePreview);

                document.querySelectorAll('input[name="qetaa_id[]"]').forEach(cb => {
                    cb.addEventListener('change', refreshAutoNamePreview);
                });

                $(document).ready(function() {
                    if (typeof $.fn.select2 === 'function') {
                        $('#event_type_id').select2({
                            theme: "classic",
                            placeholder: "اختر نوع الحدث أو المناسبة الكشفية",
                            dir: "rtl"
                        });
                    }

                    $('input[name="qetaa_id[]"]').on('change', function() {
                        const label = $(this).parent();
                        if ($(this).is(':checked')) {
                            label.addClass('bg-blue-50 border-l-4 border-blue-500');
                        } else {
                            label.removeClass('bg-blue-50 border-l-4 border-blue-500');
                        }
                        if ($('input[name="qetaa_id[]"]:checked').length > 0) {
                            qetaaErr.classList.add('hidden');
                        }
                    });

                    $('#regForm').on('submit', async function(e) {
                        const eventType = $('#event_type_id').val();
                        const qetaaChecked = $('input[name="qetaa_id[]"]:checked').length;

                        if (!eventType || qetaaChecked === 0) {
                            e.preventDefault();
                            if (qetaaChecked === 0) qetaaErr.classList.remove('hidden');
                            alert('يرجى ملء جميع الحقول المطلوبة');
                            return false;
                        }

                        if (!isRecursive.checked) {
                            const validDates = await handleMissingEndDate();
                            if (!validDates) {
                                e.preventDefault();
                                return false;
                            }
                        } else {
                            const inputs = getAllDateInputs();
                            if (inputs.length === 0) {
                                e.preventDefault();
                                multiErr.classList.remove('hidden');
                                alert('يرجى إضافة يوم واحد على الأقل');
                                return false;
                            }

                            for (const r of inputs) {
                                if (!r.value) {
                                    e.preventDefault();
                                    multiErr.classList.remove('hidden');
                                    alert('يرجى تعبئة جميع الأيام أو حذف الصفوف الفارغة');
                                    return false;
                                }
                            }

                            if (hasAnyDuplicates()) {
                                e.preventDefault();
                                showDuplicateError();
                                alert('لا يمكن تكرار نفس اليوم أكثر من مرة.');
                                return false;
                            }

                            hideDuplicateError();

                            // For recursive mode, if name empty, generate from first and last chosen day
                            if (!eventNameInput.value.trim()) {
                                const values = inputs.map(i => i.value).filter(Boolean).sort();
                                const firstDate = values[0] || '';
                                const lastDate = values[values.length - 1] || firstDate;

                                const eventTypeName = getSelectedEventTypeName();
                                const qetaaNames = getSelectedQetaaNames();

                                const generated = [
                                    eventTypeName,
                                    qetaaNames.join(' - '),
                                    firstDate && lastDate ? (firstDate + ' إلى ' + lastDate) :
                                    firstDate
                                ].filter(Boolean).join(' - ');

                                if (!generated) {
                                    e.preventDefault();
                                    alert('لا يمكن تكوين اسم الحدث تلقائياً');
                                    return false;
                                }

                                eventNameInput.value = generated;
                            }
                        }

                        if (!fillAutomaticNameIfNeeded()) {
                            e.preventDefault();
                            return false;
                        }
                    });
                });

                refreshMode();
                refreshAutoNamePreview();
            })();
        </script>
    @endpush
@endsection
