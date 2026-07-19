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
                                @if (!empty($e->TakesReservation)) ({{ __('Takes reservation') }}) @endif
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
            @if (!empty($takesReservation))
                <div class="mb-4 text-center text-sm font-semibold text-emerald-700 dark:text-emerald-300">
                    {{ __('Reservation event — mark Present, Absent, or Outside') }}
                </div>
            @endif

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
                        <div id="manualPrefixChips" class="flex flex-wrap gap-2 mb-3">
                            <button type="button" data-prefix="SHAM:" class="manual-prefix-chip h-9 px-3 rounded-full text-xs font-bold border transition bg-slate-800 text-white border-slate-800">SHAM</button>
                            @if (!empty($takesReservation))
                                <button type="button" data-prefix="GUEST:" class="manual-prefix-chip h-9 px-3 rounded-full text-xs font-bold border transition bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 border-slate-300 dark:border-slate-600">GUEST</button>
                                <button type="button" data-prefix="FAM:" class="manual-prefix-chip h-9 px-3 rounded-full text-xs font-bold border transition bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 border-slate-300 dark:border-slate-600">FAM</button>
                            @endif
                        </div>
                        <div class="flex gap-2">
                            <div class="flex-1 flex items-stretch rounded-lg border border-slate-200 dark:border-slate-700 overflow-hidden focus-within:border-blue-500">
                                <span id="manualPrefixLabel" class="inline-flex items-center px-3 text-xs font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 border-l border-slate-200 dark:border-slate-700">SHAM:</span>
                                <input id="manualCode" type="text" inputmode="numeric" autocomplete="off"
                                    placeholder="{{ __('ID number only') }}"
                                    class="flex-1 h-11 px-3 dark:bg-slate-900 dark:text-slate-100 focus:outline-none text-sm">
                            </div>
                            <button type="button" id="manualLookupBtn"
                                class="h-11 px-4 text-sm font-medium rounded-lg bg-slate-800 dark:bg-slate-100 text-white dark:text-slate-900 hover:opacity-90 transition">
                                {{ __('Look up') }}
                            </button>
                        </div>
                    </div>
                </div>

                <div id="personPanel" class="bg-white dark:bg-slate-900 rounded-lg shadow-lg dark:border dark:border-slate-700 p-6 border-2 border-blue-300 dark:border-slate-700">
                    <h2 class="text-lg font-semibold text-slate-800 dark:text-slate-100 mb-4">{{ __('Basic information') }}</h2>

                    <div id="detectBanner" class="hidden mb-4 rounded-xl px-4 py-3 text-center font-bold text-base transition-all bg-emerald-600 text-white shadow-lg">
                        <div id="detectBannerTitle">{{ __('Person detected') }}</div>
                        <div id="detectBannerName" class="text-xl mt-1"></div>
                        <div id="detectBannerHint" class="text-sm font-medium mt-1 opacity-90">{{ __('Detected successfully — choose a status below') }}</div>
                    </div>

                    <div id="personEmpty" class="text-center text-slate-500 dark:text-slate-400 py-10">
                        {{ __('Scan QR for attendance') }}
                    </div>

                    <div id="personCard" class="hidden space-y-4">
                        <div class="rounded-lg bg-slate-50 dark:bg-slate-800/60 p-4 space-y-3 ring-2 ring-emerald-400">
                            <div class="flex justify-between gap-3 text-sm">
                                <span class="text-slate-500 dark:text-slate-400">{{ __('Full name') }}</span>
                                <span id="cardName" class="font-semibold text-slate-800 dark:text-slate-100 text-left text-lg"></span>
                            </div>
                            <div class="flex justify-between gap-3 text-sm">
                                <span class="text-slate-500 dark:text-slate-400">{{ __('Type') }}</span>
                                <span id="cardType" class="font-semibold text-slate-800 dark:text-slate-100 text-left"></span>
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
                                <span class="text-slate-500 dark:text-slate-400">{{ __('Code') }}</span>
                                <span id="cardId" class="font-semibold text-slate-800 dark:text-slate-100 text-left"></span>
                            </div>
                        </div>

                        <p id="cardMessage" class="text-sm text-center font-medium"></p>

                        <div id="reservationActions" class="{{ !empty($takesReservation) ? '' : 'hidden' }} grid grid-cols-1 sm:grid-cols-3 gap-2">
                            <button type="button" data-status="present" class="status-action h-12 rounded-full bg-green-600 text-white font-medium hover:bg-green-700 transition">
                                {{ __('Present') }}
                            </button>
                            <button type="button" data-status="absent" class="status-action h-12 rounded-full bg-red-600 text-white font-medium hover:bg-red-700 transition">
                                {{ __('Absent') }}
                            </button>
                            <button type="button" data-status="outside" class="status-action h-12 rounded-full bg-amber-500 text-white font-medium hover:bg-amber-600 transition">
                                {{ __('Outside') }}
                            </button>
                        </div>

                        <div id="attendanceActions" class="{{ empty($takesReservation) ? '' : 'hidden' }} flex flex-col sm:flex-row gap-3">
                            <button type="button" id="markPresentBtn"
                                class="flex-1 h-12 rounded-full bg-green-600 text-white font-medium hover:bg-green-700 transition">
                                {{ __('Mark present') }}
                            </button>
                        </div>
                    </div>

                    <div id="lookupError" class="hidden mt-4 p-3 rounded-lg bg-red-100 dark:bg-red-900/40 text-red-800 dark:text-red-200 text-sm text-center"></div>
                </div>
            </div>

            <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const seasonEventId = @json((int) $seasonEventId);
                    const takesReservation = @json(!empty($takesReservation));
                    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
                    const lookupUrl = @json(route('attendance.lookup'));
                    const markUrl = @json(route('attendance.mark-status'));
                    let manualPrefix = 'SHAM:';

                    const statusLabels = {
                        present: @json(__('Present')),
                        absent: @json(__('Absent')),
                        outside: @json(__('Outside')),
                        excused: @json(__('~ Excuse')),
                        none: '—',
                    };

                    let currentPersonId = null;
                    let currentBookingId = null;
                    let currentEntityType = 'PERSON';
                    let html5QrCode = null;
                    let lastScanAt = 0;
                    let lastCode = '';
                    let lookupInFlight = false;

                    const els = {
                        startBtn: document.getElementById('startCameraBtn'),
                        stopBtn: document.getElementById('stopCameraBtn'),
                        scanStatus: document.getElementById('scanStatus'),
                        manualCode: document.getElementById('manualCode'),
                        manualLookupBtn: document.getElementById('manualLookupBtn'),
                        personEmpty: document.getElementById('personEmpty'),
                        personCard: document.getElementById('personCard'),
                        personPanel: document.getElementById('personPanel'),
                        detectBanner: document.getElementById('detectBanner'),
                        detectBannerName: document.getElementById('detectBannerName'),
                        detectBannerTitle: document.getElementById('detectBannerTitle'),
                        cardName: document.getElementById('cardName'),
                        cardType: document.getElementById('cardType'),
                        cardPhone: document.getElementById('cardPhone'),
                        cardQetaa: document.getElementById('cardQetaa'),
                        cardStage: document.getElementById('cardStage'),
                        cardStatus: document.getElementById('cardStatus'),
                        cardId: document.getElementById('cardId'),
                        cardMessage: document.getElementById('cardMessage'),
                        markPresentBtn: document.getElementById('markPresentBtn'),
                        lookupError: document.getElementById('lookupError'),
                        manualPrefixLabel: document.getElementById('manualPrefixLabel'),
                    };

                    function setManualPrefix(prefix) {
                        manualPrefix = prefix;
                        if (els.manualPrefixLabel) els.manualPrefixLabel.textContent = prefix;
                        document.querySelectorAll('.manual-prefix-chip').forEach((btn) => {
                            const on = btn.getAttribute('data-prefix') === prefix;
                            btn.classList.toggle('bg-slate-800', on);
                            btn.classList.toggle('text-white', on);
                            btn.classList.toggle('border-slate-800', on);
                            btn.classList.toggle('bg-white', !on);
                            btn.classList.toggle('dark:bg-slate-900', !on);
                            btn.classList.toggle('text-slate-700', !on);
                            btn.classList.toggle('dark:text-slate-200', !on);
                            btn.classList.toggle('border-slate-300', !on);
                            btn.classList.toggle('dark:border-slate-600', !on);
                        });
                        els.manualCode?.focus();
                    }

                    function buildManualCode() {
                        const raw = (els.manualCode?.value || '').trim();
                        if (!raw) return '';
                        if (/^(SHAM|GUEST|FAM)\s*:/i.test(raw)) return raw;
                        const id = raw.replace(/[^\d]/g, '');
                        return id ? (manualPrefix + id) : raw;
                    }

                    function beep() {
                        try {
                            const ctx = new (window.AudioContext || window.webkitAudioContext)();
                            const o = ctx.createOscillator();
                            const g = ctx.createGain();
                            o.type = 'sine';
                            o.frequency.value = 880;
                            g.gain.value = 0.08;
                            o.connect(g);
                            g.connect(ctx.destination);
                            o.start();
                            setTimeout(() => { o.stop(); ctx.close(); }, 140);
                        } catch (e) {}
                    }

                    function flashDetected() {
                        els.detectBanner?.classList.remove('hidden');
                        els.personPanel?.classList.remove('border-blue-300', 'dark:border-slate-700');
                        els.personPanel?.classList.add('border-emerald-500', 'ring-4', 'ring-emerald-300');
                        els.personPanel?.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                        setTimeout(() => {
                            els.personPanel?.classList.remove('ring-4', 'ring-emerald-300');
                        }, 1200);
                    }

                    function showError(msg) {
                        els.lookupError.textContent = msg || '';
                        els.lookupError.classList.toggle('hidden', !msg);
                    }

                    function showPerson(person, status, bookingId = null, { again = false } = {}) {
                        currentPersonId = person.PersonID;
                        currentBookingId = bookingId;
                        currentEntityType = person.EntityType || 'PERSON';
                        els.personEmpty.classList.add('hidden');
                        els.personCard.classList.remove('hidden');
                        els.cardName.textContent = person.PersonName || '—';
                        els.cardType.textContent = person.BookingTypeLabel || '—';
                        els.cardPhone.textContent = person.PhoneNumber || '—';
                        els.cardQetaa.textContent = person.QetaaName || '—';
                        els.cardStage.textContent = person.SanaMarhalaName || '—';
                        els.cardStatus.textContent = statusLabels[status] || statusLabels.none;
                        const prefix = currentEntityType === 'GUEST' ? 'GUEST:' : (currentEntityType === 'FAMILY' ? 'FAM:' : 'SHAM:');
                        els.cardId.textContent = prefix + person.PersonID;
                        els.detectBannerName.textContent = person.PersonName || '';
                        els.detectBannerTitle.textContent = again
                            ? @json(__('Same code scanned again'))
                            : @json(__('Person detected'));
                        els.cardMessage.textContent = @json(__('Detected successfully — choose a status below'));
                        els.cardMessage.className = 'text-sm text-center font-bold text-emerald-700 dark:text-emerald-300';
                        flashDetected();
                        beep();
                    }

                    async function lookup(code) {
                        const value = (code || '').trim();
                        if (!value || lookupInFlight) return;
                        lookupInFlight = true;
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
                                els.detectBanner?.classList.add('hidden');
                                els.personEmpty.classList.remove('hidden');
                                return;
                            }
                            const again = String(data.person?.PersonID) === String(currentPersonId)
                                && String(data.booking_id || '') === String(currentBookingId || '');
                            showPerson(data.person, data.status, data.booking_id || null, { again });
                        } catch (e) {
                            showError(@json(__('Invalid QR code.')));
                        } finally {
                            els.scanStatus.textContent = @json(__('Scanning…'));
                            lookupInFlight = false;
                        }
                    }

                    async function markStatus(status) {
                        showError('');
                        const body = {
                            season_event_id: seasonEventId,
                            status,
                        };
                        if (takesReservation) {
                            if (!currentBookingId) return;
                            body.booking_id = currentBookingId;
                        } else {
                            if (!currentPersonId) return;
                            body.person_id = currentPersonId;
                        }

                        try {
                            const res = await fetch(markUrl, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': csrf,
                                    'X-Requested-With': 'XMLHttpRequest',
                                },
                                body: JSON.stringify(body),
                            });
                            const data = await res.json();
                            if (!res.ok || !data.ok) {
                                showError(data.error || @json(__('Not allowed to take attendance for this event')));
                                return;
                            }
                            els.cardStatus.textContent = statusLabels[data.status] || data.status;
                            els.cardMessage.textContent = data.message || @json(__('Attendance updated successfully.'));
                            els.cardMessage.className = 'text-sm text-center font-medium text-green-700 dark:text-green-300';
                        } catch (e) {
                            showError(@json(__('Not allowed to take attendance for this event')));
                        }
                    }

                    async function onScanSuccess(decodedText) {
                        const now = Date.now();
                        // Faster re-scan: allow same code after 900ms; ignore while request in flight
                        if (lookupInFlight) return;
                        if (decodedText === lastCode && now - lastScanAt < 900) return;
                        lastCode = decodedText;
                        lastScanAt = now;
                        els.scanStatus.textContent = @json(__('Person detected')) + ': ' + decodedText;
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
                                {
                                    fps: 20,
                                    qrbox: (viewfinderWidth, viewfinderHeight) => {
                                        const edge = Math.floor(Math.min(viewfinderWidth, viewfinderHeight) * 0.72);
                                        return { width: edge, height: edge };
                                    },
                                    aspectRatio: 1.0,
                                    disableFlip: false,
                                },
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
                    els.markPresentBtn?.addEventListener('click', () => markStatus('present'));
                    document.querySelectorAll('.status-action').forEach((btn) => {
                        btn.addEventListener('click', () => markStatus(btn.getAttribute('data-status')));
                    });
                    document.querySelectorAll('.manual-prefix-chip').forEach((btn) => {
                        btn.addEventListener('click', () => setManualPrefix(btn.getAttribute('data-prefix')));
                    });
                    setManualPrefix('SHAM:');
                    els.manualLookupBtn?.addEventListener('click', () => lookup(buildManualCode()));
                    els.manualCode?.addEventListener('keydown', (e) => {
                        if (e.key === 'Enter') {
                            e.preventDefault();
                            lookup(buildManualCode());
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
