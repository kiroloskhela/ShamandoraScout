@extends('layouts.app', ['pageTitle' => __('Scan attendance')])

@section('content')
    <div class="container mx-auto px-4 py-8" dir="rtl">

        <div class="mb-8 text-center">
            <h1 class="text-3xl font-bold text-gray-800 dark:text-slate-100 mb-2">{{ __('Scan attendance') }}</h1>
            <p class="text-gray-600 dark:text-slate-300">{{ __('Choose season and event, then scan the person QR code') }}</p>
            <div class="mt-4 flex flex-wrap items-center justify-center gap-3">
                <a href="{{ route('attendance.manage', ['season_id' => $seasonId, 'season_event_id' => $seasonEventId]) }}"
                    class="inline-flex items-center h-10 px-4 text-sm font-medium rounded-full border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                    {{ __('Record attendance') }}
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="mb-6 p-4 rounded-lg bg-green-100 dark:bg-green-900/40 text-green-800 dark:text-green-200 text-center font-semibold shadow dark:border dark:border-slate-700">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="mb-6 p-4 rounded-lg bg-red-100 dark:bg-red-900/40 text-red-800 dark:text-red-200 text-center font-semibold shadow dark:border dark:border-slate-700">
                {{ session('error') }}
            </div>
        @endif

        <form method="GET" action="{{ route('attendance.scan') }}"
            class="bg-white dark:bg-slate-900 rounded-lg shadow-lg dark:border dark:border-slate-700 p-6 mb-8 border-2 border-blue-300 dark:border-slate-700">
            <div class="grid md:grid-cols-2 gap-6">
                <div>
                    <label for="season_id" class="block mb-2 text-sm font-semibold text-gray-700 dark:text-slate-200">{{ __('Choose season') }}</label>
                    <select id="season_id" name="season_id"
                        class="w-full h-12 px-4 border rounded-lg text-right border-slate-200 dark:border-slate-700 dark:bg-slate-900 text-slate-600 dark:text-slate-300 focus:border-blue-500 focus:outline-none"
                        onchange="this.form.submit()">
                        <option value="">{{ __('-- Choose season --') }}</option>
                        @foreach ($seasons as $s)
                            <option value="{{ $s->SeasonID }}" {{ ($seasonId ?? null) == $s->SeasonID ? 'selected' : '' }}>
                                {{ $s->SeasonName }} ({{ $s->SeasonYear }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="season_event_id" class="block mb-2 text-sm font-semibold text-gray-700 dark:text-slate-200">{{ __('Choose event') }}</label>
                    <select id="season_event_id" name="season_event_id"
                        class="w-full h-12 px-4 border rounded-lg text-right border-slate-200 dark:border-slate-700 dark:bg-slate-900 {{ !empty($seasonId) ? 'text-slate-600 dark:text-slate-300' : 'text-slate-400 dark:text-slate-500' }} focus:border-blue-500 focus:outline-none"
                        {{ !empty($seasonId) ? '' : 'disabled' }} onchange="this.form.submit()">
                        <option value="">{{ __('-- Choose event --') }}</option>
                        @foreach ($events as $e)
                            <option value="{{ $e->SeasonEventID }}"
                                {{ ($seasonEventId ?? null) == $e->SeasonEventID ? 'selected' : '' }}>
                                {{ $e->EventName }} - {{ $e->EventStartDate }}
                            </option>
                        @endforeach
                    </select>
                    @if (($seasonId ?? null) && $events->isEmpty())
                        <p class="mt-2 text-xs text-amber-600 dark:text-amber-400">{{ __('No events for your groups in this season.') }}</p>
                    @endif
                </div>
            </div>
        </form>

        @if (!empty($seasonEventId))
            <div class="mb-6 flex flex-wrap items-center justify-center gap-3">
                <form method="POST" action="{{ route('attendance.send-qr-bulk') }}"
                    onsubmit="return confirm(@json(__('Confirm send QR codes to all people on this event roster via WhatsApp?')))">
                    @csrf
                    <input type="hidden" name="season_id" value="{{ $seasonId }}">
                    <input type="hidden" name="season_event_id" value="{{ $seasonEventId }}">
                    <button type="submit"
                        class="inline-flex items-center justify-center h-11 px-5 text-sm font-medium rounded-full bg-emerald-600 text-white hover:bg-emerald-700 transition">
                        {{ __('Send QR codes via WhatsApp') }}
                    </button>
                </form>
            </div>

            <div class="grid lg:grid-cols-2 gap-6">
                <div class="bg-white dark:bg-slate-900 rounded-lg shadow-lg dark:border dark:border-slate-700 p-6 border-2 border-blue-300 dark:border-slate-700">
                    <div class="flex items-center justify-between gap-3 mb-4">
                        <h2 class="text-lg font-semibold text-slate-800 dark:text-slate-100">{{ __('Scan QR for attendance') }}</h2>
                        <div class="flex gap-2">
                            <button type="button" id="startCameraBtn"
                                class="h-10 px-4 text-sm font-medium rounded-full bg-blue-600 text-white hover:bg-blue-700 transition">{{ __('Start camera') }}</button>
                            <button type="button" id="stopCameraBtn"
                                class="h-10 px-4 text-sm font-medium rounded-full border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 transition hidden">{{ __('Stop camera') }}</button>
                        </div>
                    </div>

                    <div id="qr-reader" class="w-full overflow-hidden rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 min-h-[240px]"></div>
                    <p id="scanStatus" class="mt-3 text-sm text-slate-500 dark:text-slate-400 text-center"></p>

                    <div class="mt-5 pt-5 border-t border-slate-200 dark:border-slate-700">
                        <label for="manualCode" class="block mb-2 text-sm font-semibold text-gray-700 dark:text-slate-200">{{ __('Or enter code manually') }}</label>
                        <div class="flex gap-2">
                            <input id="manualCode" type="text" inputmode="numeric" autocomplete="off"
                                placeholder="SHAM:123"
                                class="flex-1 h-11 px-4 rounded-lg border border-slate-200 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 focus:border-blue-500 focus:outline-none text-sm">
                            <button type="button" id="manualLookupBtn"
                                class="h-11 px-4 text-sm font-medium rounded-lg bg-slate-800 dark:bg-slate-100 text-white dark:text-slate-900 hover:opacity-90 transition">
                                {{ __('Look up') }}
                            </button>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-slate-900 rounded-lg shadow-lg dark:border dark:border-slate-700 p-6 border-2 border-blue-300 dark:border-slate-700">
                    <h2 class="text-lg font-semibold text-slate-800 dark:text-slate-100 mb-4">{{ __('Basic information') }}</h2>

                    <div id="personEmpty" class="text-center text-slate-500 dark:text-slate-400 py-10">
                        {{ __('Scan QR for attendance') }}
                    </div>

                    <div id="personCard" class="hidden space-y-4">
                        <div class="rounded-lg bg-slate-50 dark:bg-slate-800/60 p-4 space-y-3">
                            <div class="flex justify-between gap-3 text-sm">
                                <span class="text-slate-500 dark:text-slate-400">{{ __('Full name') }}</span>
                                <span id="cardName" class="font-semibold text-slate-800 dark:text-slate-100 text-left"></span>
                            </div>
                            <div class="flex justify-between gap-3 text-sm">
                                <span class="text-slate-500 dark:text-slate-400">{{ __('Phone') }}</span>
                                <span id="cardPhone" class="font-semibold text-slate-800 dark:text-slate-100 text-left dir-ltr"></span>
                            </div>
                            <div class="flex justify-between gap-3 text-sm">
                                <span class="text-slate-500 dark:text-slate-400">{{ __('Sector') }}</span>
                                <span id="cardQetaa" class="font-semibold text-slate-800 dark:text-slate-100 text-left"></span>
                            </div>
                            <div class="flex justify-between gap-3 text-sm">
                                <span class="text-slate-500 dark:text-slate-400">{{ __('Stage') }}</span>
                                <span id="cardStage" class="font-semibold text-slate-800 dark:text-slate-100 text-left"></span>
                            </div>
                            <div class="flex justify-between gap-3 text-sm">
                                <span class="text-slate-500 dark:text-slate-400">{{ __('Status') }}</span>
                                <span id="cardStatus" class="font-semibold text-slate-800 dark:text-slate-100 text-left"></span>
                            </div>
                            <div class="flex justify-between gap-3 text-sm">
                                <span class="text-slate-500 dark:text-slate-400">{{ __('Person ID') }}</span>
                                <span id="cardId" class="font-semibold text-slate-800 dark:text-slate-100 text-left"></span>
                            </div>
                        </div>

                        <p id="cardMessage" class="text-sm text-center font-medium"></p>

                        <div class="flex flex-col sm:flex-row gap-3">
                            <button type="button" id="markPresentBtn"
                                class="flex-1 h-12 rounded-full bg-green-600 text-white font-medium hover:bg-green-700 transition">
                                {{ __('Mark present') }}
                            </button>
                            <form id="sendQrForm" method="POST" action="#" class="flex-1">
                                @csrf
                                <input type="hidden" name="season_event_id" value="{{ $seasonEventId }}">
                                <button type="submit"
                                    class="w-full h-12 rounded-full border border-emerald-600 text-emerald-700 dark:text-emerald-300 font-medium hover:bg-emerald-50 dark:hover:bg-emerald-950/40 transition">
                                    {{ __('Send QR code via WhatsApp') }}
                                </button>
                            </form>
                        </div>
                    </div>

                    <div id="lookupError" class="hidden mt-4 p-3 rounded-lg bg-red-100 dark:bg-red-900/40 text-red-800 dark:text-red-200 text-sm text-center"></div>
                </div>
            </div>

            <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const seasonEventId = @json((int) $seasonEventId);
                    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
                    const lookupUrl = @json(route('attendance.lookup'));
                    const markUrl = @json(route('attendance.mark-present'));
                    const sendQrUrlTemplate = @json(url('/attendance/send-qr'));

                    const statusLabels = {
                        present: @json(__('✓ Present')),
                        absent: @json(__('✗ Absent')),
                        excused: @json(__('~ Excuse')),
                        none: '—',
                    };

                    let currentPersonId = null;
                    let html5QrCode = null;
                    let lastScanAt = 0;
                    let lastCode = '';

                    const els = {
                        startBtn: document.getElementById('startCameraBtn'),
                        stopBtn: document.getElementById('stopCameraBtn'),
                        scanStatus: document.getElementById('scanStatus'),
                        manualCode: document.getElementById('manualCode'),
                        manualLookupBtn: document.getElementById('manualLookupBtn'),
                        personEmpty: document.getElementById('personEmpty'),
                        personCard: document.getElementById('personCard'),
                        cardName: document.getElementById('cardName'),
                        cardPhone: document.getElementById('cardPhone'),
                        cardQetaa: document.getElementById('cardQetaa'),
                        cardStage: document.getElementById('cardStage'),
                        cardStatus: document.getElementById('cardStatus'),
                        cardId: document.getElementById('cardId'),
                        cardMessage: document.getElementById('cardMessage'),
                        markPresentBtn: document.getElementById('markPresentBtn'),
                        sendQrForm: document.getElementById('sendQrForm'),
                        lookupError: document.getElementById('lookupError'),
                    };

                    function showError(msg) {
                        els.lookupError.textContent = msg || '';
                        els.lookupError.classList.toggle('hidden', !msg);
                    }

                    function showPerson(person, status) {
                        currentPersonId = person.PersonID;
                        els.personEmpty.classList.add('hidden');
                        els.personCard.classList.remove('hidden');
                        els.cardName.textContent = person.PersonName || '—';
                        els.cardPhone.textContent = person.PhoneNumber || '—';
                        els.cardQetaa.textContent = person.QetaaName || '—';
                        els.cardStage.textContent = person.SanaMarhalaName || '—';
                        els.cardStatus.textContent = statusLabels[status] || statusLabels.none;
                        els.cardId.textContent = String(person.PersonID);
                        els.sendQrForm.action = sendQrUrlTemplate + '/' + person.PersonID;

                        if (status === 'present') {
                            els.cardMessage.textContent = @json(__('Already marked present'));
                            els.cardMessage.className = 'text-sm text-center font-medium text-amber-700 dark:text-amber-300';
                            els.markPresentBtn.disabled = true;
                            els.markPresentBtn.classList.add('opacity-50', 'cursor-not-allowed');
                        } else {
                            els.cardMessage.textContent = '';
                            els.cardMessage.className = 'text-sm text-center font-medium';
                            els.markPresentBtn.disabled = false;
                            els.markPresentBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                        }
                    }

                    async function lookup(code) {
                        const value = (code || '').trim();
                        if (!value) return;
                        showError('');
                        els.scanStatus.textContent = @json(__('Scanning…'));

                        try {
                            const res = await fetch(lookupUrl, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': csrf,
                                    'X-Requested-With': 'XMLHttpRequest',
                                },
                                body: JSON.stringify({
                                    season_event_id: seasonEventId,
                                    code: value,
                                }),
                            });
                            const data = await res.json();
                            if (!res.ok || !data.ok) {
                                showError(data.error || @json(__('Invalid QR code.')));
                                els.personCard.classList.add('hidden');
                                els.personEmpty.classList.remove('hidden');
                                return;
                            }
                            showPerson(data.person, data.status);
                        } catch (e) {
                            showError(@json(__('Invalid QR code.')));
                        } finally {
                            els.scanStatus.textContent = '';
                        }
                    }

                    async function markPresent() {
                        if (!currentPersonId) return;
                        showError('');
                        els.markPresentBtn.disabled = true;

                        try {
                            const res = await fetch(markUrl, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': csrf,
                                    'X-Requested-With': 'XMLHttpRequest',
                                },
                                body: JSON.stringify({
                                    season_event_id: seasonEventId,
                                    person_id: currentPersonId,
                                }),
                            });
                            const data = await res.json();
                            if (!res.ok || !data.ok) {
                                showError(data.error || @json(__('Not allowed to take attendance for this event')));
                                els.markPresentBtn.disabled = false;
                                return;
                            }
                            els.cardStatus.textContent = statusLabels.present;
                            els.cardMessage.textContent = data.message || @json(__('Marked present successfully.'));
                            els.cardMessage.className = 'text-sm text-center font-medium text-green-700 dark:text-green-300';
                            els.markPresentBtn.classList.add('opacity-50', 'cursor-not-allowed');
                        } catch (e) {
                            showError(@json(__('Not allowed to take attendance for this event')));
                            els.markPresentBtn.disabled = false;
                        }
                    }

                    async function onScanSuccess(decodedText) {
                        const now = Date.now();
                        if (decodedText === lastCode && now - lastScanAt < 2500) return;
                        lastCode = decodedText;
                        lastScanAt = now;
                        await lookup(decodedText);
                    }

                    async function startCamera() {
                        if (!window.Html5Qrcode) {
                            els.scanStatus.textContent = @json(__('Camera permission denied or unavailable.'));
                            return;
                        }
                        if (!html5QrCode) {
                            html5QrCode = new Html5Qrcode('qr-reader');
                        }
                        try {
                            await html5QrCode.start(
                                { facingMode: 'environment' },
                                { fps: 10, qrbox: { width: 250, height: 250 } },
                                onScanSuccess,
                                () => {}
                            );
                            els.startBtn.classList.add('hidden');
                            els.stopBtn.classList.remove('hidden');
                            els.scanStatus.textContent = @json(__('Scanning…'));
                        } catch (e) {
                            els.scanStatus.textContent = @json(__('Camera permission denied or unavailable.'));
                        }
                    }

                    async function stopCamera() {
                        if (!html5QrCode) return;
                        try {
                            await html5QrCode.stop();
                            await html5QrCode.clear();
                        } catch (e) {}
                        els.startBtn.classList.remove('hidden');
                        els.stopBtn.classList.add('hidden');
                        els.scanStatus.textContent = '';
                    }

                    els.startBtn?.addEventListener('click', startCamera);
                    els.stopBtn?.addEventListener('click', stopCamera);
                    els.markPresentBtn?.addEventListener('click', markPresent);
                    els.manualLookupBtn?.addEventListener('click', () => lookup(els.manualCode.value));
                    els.manualCode?.addEventListener('keydown', (e) => {
                        if (e.key === 'Enter') {
                            e.preventDefault();
                            lookup(els.manualCode.value);
                        }
                    });

                    window.addEventListener('beforeunload', () => {
                        if (html5QrCode) {
                            html5QrCode.stop().catch(() => {});
                        }
                    });
                });
            </script>
        @endif
    </div>
@endsection
