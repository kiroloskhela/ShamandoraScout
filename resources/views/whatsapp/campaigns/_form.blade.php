@php
    $isEdit = isset($campaign);
    $action = $isEdit ? route('whatsapp.campaigns.update', $campaign) : route('whatsapp.campaigns.store');
    $method = $isEdit ? 'PUT' : 'POST';
    $selectedIds = $selectedIds ?? [];
    $initialTemplate = old('message_template', $campaign->message_template ?? '');
@endphp

<form method="POST" action="{{ $action }}" id="campaign-form"
    x-data="waCampaignForm({
        searchUrl: @js(route('whatsapp.campaigns.contacts.search')),
        previewUrl: @js(route('whatsapp.campaigns.preview')),
        csrf: @js(csrf_token()),
        initialSelected: @js($selectedIds),
        variables: @js($variables),
        highCount: {{ (int) $highCountThreshold }},
        initialTemplate: @js($initialTemplate),
        labels: {
            allMatching: @js(__('All matching')),
            searching: @js(__('Searching…')),
            noResults: @js(__('No results found')),
            searchHint: @js(__('Type a name, phone, or code to search')),
            missingPrefix: @js(__('Missing variables:')),
            friend: @js(__('Our friend')),
        }
    })"
    @submit="prepareSubmit()"
    class="space-y-6" dir="rtl">
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <template x-for="id in selected" :key="'sel-' + id">
        <input type="hidden" name="person_ids[]" :value="id">
    </template>
    <input type="hidden" name="select_all" :value="selectAll ? '1' : '0'">
    <input type="hidden" name="filter_q" :value="filters.q">
    <input type="hidden" name="filter_gender" :value="filters.gender">
    <input type="hidden" name="filter_qetaa_id" :value="filters.qetaa_id">
    <input type="hidden" name="filter_group_id" :value="filters.group_id">
    <input type="hidden" name="filter_manteqa_id" :value="filters.manteqa_id">
    <input type="hidden" name="filter_district_id" :value="filters.district_id">
    <template x-if="filters.has_whatsapp">
        <input type="hidden" name="filter_has_whatsapp" value="1">
    </template>

    {{-- Step 1 --}}
    <section class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 shadow-sm overflow-hidden">
        <header class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center gap-3 bg-slate-50/80 dark:bg-slate-800/40">
            <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-emerald-600 text-white text-sm font-bold">1</span>
            <div>
                <h2 class="text-base font-bold text-slate-900 dark:text-slate-100">{{ __('Campaign details') }}</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('Name and send-rate limits') }}</p>
            </div>
        </header>
        <div class="p-6 space-y-5">
            <div>
                <label for="campaign_name" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-2">{{ __('Campaign name') }}</label>
                <input id="campaign_name" type="text" name="name" required value="{{ old('name', $campaign->name ?? '') }}"
                    placeholder="{{ __('e.g. Reminder for camp deposit') }}"
                    class="w-full h-12 px-4 rounded-xl border border-slate-200 dark:border-slate-700 dark:bg-slate-950 text-slate-800 dark:text-slate-100 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none transition">
            </div>
            <div class="grid sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-2">{{ __('Min delay (sec)') }}</label>
                    <input type="number" name="min_delay_seconds" min="1" max="600"
                        value="{{ old('min_delay_seconds', $campaign->min_delay_seconds ?? 8) }}"
                        class="w-full h-12 px-4 rounded-xl border border-slate-200 dark:border-slate-700 dark:bg-slate-950 text-slate-800 dark:text-slate-100 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none transition">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-2">{{ __('Max delay (sec)') }}</label>
                    <input type="number" name="max_delay_seconds" min="1" max="600"
                        value="{{ old('max_delay_seconds', $campaign->max_delay_seconds ?? 15) }}"
                        class="w-full h-12 px-4 rounded-xl border border-slate-200 dark:border-slate-700 dark:bg-slate-950 text-slate-800 dark:text-slate-100 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none transition">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-2">{{ __('Max per hour') }}</label>
                    <input type="number" name="max_messages_per_hour" min="1" max="500"
                        value="{{ old('max_messages_per_hour', $campaign->max_messages_per_hour ?? 60) }}"
                        class="w-full h-12 px-4 rounded-xl border border-slate-200 dark:border-slate-700 dark:bg-slate-950 text-slate-800 dark:text-slate-100 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none transition">
                </div>
            </div>
        </div>
    </section>

    {{-- Step 2 --}}
    <section class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 shadow-sm overflow-hidden">
        <header class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex flex-wrap items-center justify-between gap-3 bg-slate-50/80 dark:bg-slate-800/40">
            <div class="flex items-center gap-3">
                <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-emerald-600 text-white text-sm font-bold">2</span>
                <div>
                    <h2 class="text-base font-bold text-slate-900 dark:text-slate-100">{{ __('Choose recipients') }}</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('Search and select people with a phone number') }}</p>
                </div>
            </div>
            <div class="inline-flex items-center gap-2 rounded-full bg-emerald-50 dark:bg-emerald-950/40 text-emerald-800 dark:text-emerald-200 px-3 py-1.5 text-sm font-bold">
                <span>{{ __('Selected:') }}</span>
                <span x-text="selectedCountLabel()"></span>
            </div>
        </header>

        <div class="p-6 space-y-4">
            <div class="relative">
                <svg class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 h-5 w-5 text-slate-400" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path stroke="currentColor" stroke-width="2" stroke-linecap="round" d="m21 21-4.3-4.3m0-6.2a7.5 7.5 0 1 1-15 0 7.5 7.5 0 0 1 15 0Z" />
                </svg>
                <input type="search" x-model="filters.q" @input.debounce.350ms="search()"
                    placeholder="{{ __('Search by name / phone / code') }}"
                    class="w-full h-12 pr-11 pl-4 rounded-xl border border-slate-200 dark:border-slate-700 dark:bg-slate-950 text-slate-800 dark:text-slate-100 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none transition"
                    autocomplete="off">
            </div>

            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-3">
                <select x-model="filters.gender" @change="search()"
                    class="h-11 px-3 rounded-xl border border-slate-200 dark:border-slate-700 dark:bg-slate-950 text-sm text-slate-700 dark:text-slate-200 focus:border-emerald-500 focus:outline-none">
                    <option value="">{{ __('Gender (all)') }}</option>
                    <option value="Male">{{ __('Male') }}</option>
                    <option value="Female">{{ __('Female') }}</option>
                </select>
                <select x-model="filters.qetaa_id" @change="search()"
                    class="h-11 px-3 rounded-xl border border-slate-200 dark:border-slate-700 dark:bg-slate-950 text-sm text-slate-700 dark:text-slate-200 focus:border-emerald-500 focus:outline-none">
                    <option value="">{{ __('Sector') }}</option>
                    @foreach ($qetaat as $q)
                        <option value="{{ $q->QetaaID }}">{{ $q->QetaaName }}</option>
                    @endforeach
                </select>
                <select x-model="filters.group_id" @change="search()"
                    class="h-11 px-3 rounded-xl border border-slate-200 dark:border-slate-700 dark:bg-slate-950 text-sm text-slate-700 dark:text-slate-200 focus:border-emerald-500 focus:outline-none">
                    <option value="">{{ __('Group / service') }}</option>
                    @foreach ($groups as $g)
                        <option value="{{ $g->GroupID }}">{{ $g->GroupName }}</option>
                    @endforeach
                </select>
                <select x-model="filters.manteqa_id" @change="search()"
                    class="h-11 px-3 rounded-xl border border-slate-200 dark:border-slate-700 dark:bg-slate-950 text-sm text-slate-700 dark:text-slate-200 focus:border-emerald-500 focus:outline-none">
                    <option value="">{{ __('Area') }}</option>
                    @foreach ($manteqat as $m)
                        <option value="{{ $m->ManteqaID }}">{{ $m->ManteqaName }}</option>
                    @endforeach
                </select>
                <select x-model="filters.district_id" @change="search()"
                    class="h-11 px-3 rounded-xl border border-slate-200 dark:border-slate-700 dark:bg-slate-950 text-sm text-slate-700 dark:text-slate-200 focus:border-emerald-500 focus:outline-none">
                    <option value="">{{ __('District') }}</option>
                    @foreach ($districts as $d)
                        <option value="{{ $d->DistrictID }}">{{ $d->DistrictName }}</option>
                    @endforeach
                </select>
                <label class="h-11 px-3 rounded-xl border border-slate-200 dark:border-slate-700 dark:bg-slate-950 text-sm text-slate-700 dark:text-slate-200 inline-flex items-center gap-2 cursor-pointer hover:border-emerald-400 transition">
                    <input type="checkbox" x-model="filters.has_whatsapp" @change="search()"
                        class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                    {{ __('Has WhatsApp only') }}
                </label>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <button type="button" @click="search()" :disabled="loading"
                    class="inline-flex items-center justify-center h-10 px-4 rounded-xl bg-slate-900 dark:bg-slate-100 text-white dark:text-slate-900 text-sm font-semibold hover:opacity-90 disabled:opacity-50 disabled:cursor-not-allowed transition">
                    <span x-show="!loading">{{ __('Search') }}</span>
                    <span x-show="loading" x-cloak x-text="labels.searching"></span>
                </button>
                <button type="button" @click="selectVisible()" :disabled="!people.length"
                    class="inline-flex items-center justify-center h-10 px-4 rounded-xl bg-emerald-600 text-white text-sm font-semibold hover:bg-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed transition">
                    {{ __('Select visible') }}
                </button>
                <button type="button" @click="clearSelected()" :disabled="!selected.length && !selectAll"
                    class="inline-flex items-center justify-center h-10 px-4 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 text-sm font-semibold hover:bg-slate-50 dark:hover:bg-slate-800 disabled:opacity-50 disabled:cursor-not-allowed transition">
                    {{ __('Clear selection') }}
                </button>
                <label class="inline-flex items-center gap-2 h-10 px-3 rounded-xl border border-slate-200 dark:border-slate-700 text-sm text-slate-700 dark:text-slate-200 cursor-pointer">
                    <input type="checkbox" x-model="selectAll" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                    {{ __('Select all matching filters (up to 2000)') }}
                </label>
                <span class="text-xs text-slate-500 dark:text-slate-400" x-show="resultCount !== null" x-cloak>
                    {{ __('Results:') }} <strong x-text="resultCount"></strong>
                </span>
            </div>

            <div class="rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
                <div class="max-h-96 overflow-auto">
                    <table class="min-w-full text-sm">
                        <thead class="sticky top-0 bg-slate-100 dark:bg-slate-800 z-10">
                            <tr>
                                <th class="px-3 py-3 w-12"></th>
                                <th class="px-3 py-3 text-right font-semibold text-slate-700 dark:text-slate-200">{{ __('Name') }}</th>
                                <th class="px-3 py-3 text-right font-semibold text-slate-700 dark:text-slate-200">{{ __('Code') }}</th>
                                <th class="px-3 py-3 text-right font-semibold text-slate-700 dark:text-slate-200">{{ __('Phone') }}</th>
                                <th class="px-3 py-3 text-right font-semibold text-slate-700 dark:text-slate-200">{{ __('Sector') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="p in people" :key="p.person_id">
                                <tr class="border-t border-slate-100 dark:border-slate-800 hover:bg-emerald-50/50 dark:hover:bg-emerald-950/20 transition"
                                    :class="isSelected(p.person_id) ? 'bg-emerald-50/70 dark:bg-emerald-950/30' : ''">
                                    <td class="px-3 py-2.5">
                                        <input type="checkbox"
                                            class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500"
                                            :checked="isSelected(p.person_id)"
                                            @change="togglePerson(p.person_id, $event.target.checked)">
                                    </td>
                                    <td class="px-3 py-2.5 font-semibold text-slate-900 dark:text-slate-100" x-text="p.full_name"></td>
                                    <td class="px-3 py-2.5 font-mono text-xs text-slate-600 dark:text-slate-300" x-text="p.shamandora_code || '—'"></td>
                                    <td class="px-3 py-2.5 font-mono text-xs text-slate-700 dark:text-slate-200 dir-ltr" x-text="p.phone"></td>
                                    <td class="px-3 py-2.5 text-slate-600 dark:text-slate-300" x-text="p.qetaa || '—'"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
                <div class="px-4 py-8 text-center text-sm text-slate-500 dark:text-slate-400" x-show="!loading && people.length === 0" x-text="hasSearched ? labels.noResults : labels.searchHint"></div>
                <div class="px-4 py-8 text-center text-sm text-slate-500 dark:text-slate-400" x-show="loading" x-cloak x-text="labels.searching"></div>
            </div>
        </div>
    </section>

    {{-- Step 3 --}}
    <section class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 shadow-sm overflow-hidden">
        <header class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center gap-3 bg-slate-50/80 dark:bg-slate-800/40">
            <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-emerald-600 text-white text-sm font-bold">3</span>
            <div>
                <h2 class="text-base font-bold text-slate-900 dark:text-slate-100">{{ __('Message') }}</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">{{ __("Type { to insert a variable. Available: {name}") }}</p>
            </div>
        </header>
        <div class="p-6 space-y-4 relative">
            <div class="relative">
                <textarea name="message_template" x-ref="template" x-model="template" @keydown="onTemplateKey($event)"
                    required rows="6"
                    class="w-full rounded-xl border border-slate-200 dark:border-slate-700 dark:bg-slate-950 px-4 py-3 font-mono text-sm text-slate-800 dark:text-slate-100 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none transition"
                    placeholder="{{ __('Hello {name}, ...') }}"></textarea>
                <div x-show="showVars" x-cloak @click.outside="showVars = false"
                    class="absolute z-20 mt-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl shadow-lg w-52 overflow-hidden">
                    <template x-for="v in variables" :key="v">
                        <button type="button" @click="insertVar(v)"
                            class="block w-full text-right px-4 py-2.5 text-sm text-slate-700 dark:text-slate-200 hover:bg-emerald-50 dark:hover:bg-emerald-950/40 transition"
                            x-text="'{' + v + '}'"></button>
                    </template>
                </div>
            </div>
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-2">{{ __('Missing variable behavior') }}</label>
                    <select name="missing_variable_behavior" x-ref="missingBehavior"
                        class="w-full h-12 px-4 rounded-xl border border-slate-200 dark:border-slate-700 dark:bg-slate-950 text-slate-800 dark:text-slate-100 focus:border-emerald-500 focus:outline-none">
                        @foreach (['fallback' => __('Fallback value'), 'empty' => __('Empty'), 'skip' => __('Skip recipient'), 'warn' => __('Warn before send')] as $val => $label)
                            <option value="{{ $val }}" @selected(old('missing_variable_behavior', $campaign->missing_variable_behavior ?? 'fallback') === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-2">{{ __('Fallback name') }}</label>
                    <input type="text" name="fallback_name" x-ref="fallbackName"
                        value="{{ old('fallback_name', $campaign->fallback_name ?? __('Our friend')) }}"
                        class="w-full h-12 px-4 rounded-xl border border-slate-200 dark:border-slate-700 dark:bg-slate-950 text-slate-800 dark:text-slate-100 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none transition">
                </div>
            </div>
        </div>
    </section>

    {{-- Step 4 --}}
    <section class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 shadow-sm overflow-hidden">
        <header class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex flex-wrap items-center justify-between gap-3 bg-slate-50/80 dark:bg-slate-800/40">
            <div class="flex items-center gap-3">
                <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-emerald-600 text-white text-sm font-bold">4</span>
                <div>
                    <h2 class="text-base font-bold text-slate-900 dark:text-slate-100">{{ __('Preview') }}</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('Estimated messages:') }} <strong x-text="estimated"></strong></p>
                </div>
            </div>
            <button type="button" @click="loadPreview()"
                class="inline-flex items-center justify-center h-10 px-4 rounded-xl bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700 transition">
                {{ __('Refresh preview') }}
            </button>
        </header>
        <div class="p-6">
            <template x-if="previews.length">
                <div class="rounded-xl border border-slate-200 dark:border-slate-700 p-4 space-y-3 bg-slate-50/60 dark:bg-slate-950/40">
                    <div class="flex items-center justify-between gap-2">
                        <button type="button" @click="prevPreview()"
                            class="h-9 px-3 rounded-lg border border-slate-200 dark:border-slate-700 text-sm font-semibold text-slate-700 dark:text-slate-200 hover:bg-white dark:hover:bg-slate-900 transition">{{ __('Previous') }}</button>
                        <span class="text-sm text-slate-600 dark:text-slate-300" x-text="(previewIndex+1) + ' / ' + previews.length"></span>
                        <button type="button" @click="nextPreview()"
                            class="h-9 px-3 rounded-lg border border-slate-200 dark:border-slate-700 text-sm font-semibold text-slate-700 dark:text-slate-200 hover:bg-white dark:hover:bg-slate-900 transition">{{ __('Next') }}</button>
                    </div>
                    <div class="text-sm text-slate-500 dark:text-slate-400" x-text="currentPreview()?.full_name + ' — ' + currentPreview()?.phone"></div>
                    <pre class="whitespace-pre-wrap text-sm bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 p-4 rounded-xl text-slate-800 dark:text-slate-100" x-text="currentPreview()?.message"></pre>
                    <p class="text-amber-700 dark:text-amber-300 text-sm" x-show="currentPreview()?.missing?.length"
                        x-text="labels.missingPrefix + ' ' + (currentPreview()?.missing || []).join(', ')"></p>
                    <p class="text-red-700 dark:text-red-300 text-sm" x-show="currentPreview()?.skipped">{{ __('This recipient will be skipped') }}</p>
                </div>
            </template>
            <p class="text-sm text-slate-500 dark:text-slate-400" x-show="!previews.length">{{ __('Select recipients and write a message, then refresh the preview.') }}</p>
        </div>
    </section>

    <div class="flex flex-wrap items-center gap-3 sticky bottom-4 z-20">
        <button type="submit"
            class="inline-flex items-center justify-center h-12 px-8 rounded-full bg-emerald-600 text-white font-bold shadow-lg shadow-emerald-600/20 hover:bg-emerald-700 active:scale-[0.98] transition">
            {{ __('Save draft') }}
        </button>
        <a href="{{ route('whatsapp.campaigns.index') }}"
            class="inline-flex items-center justify-center h-12 px-6 rounded-full border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 font-semibold hover:bg-slate-50 dark:hover:bg-slate-800 transition">
            {{ __('Cancel') }}
        </a>
    </div>
</form>

<script>
function waCampaignForm(cfg) {
    return {
        searchUrl: cfg.searchUrl,
        previewUrl: cfg.previewUrl,
        csrf: cfg.csrf,
        variables: cfg.variables || ['name'],
        highCount: cfg.highCount || 100,
        labels: cfg.labels || {},
        filters: { q: '', gender: '', qetaa_id: '', group_id: '', manteqa_id: '', district_id: '', has_whatsapp: false },
        people: [],
        selected: (cfg.initialSelected || []).map(String),
        selectAll: false,
        template: cfg.initialTemplate || '',
        showVars: false,
        previews: [],
        previewIndex: 0,
        estimated: 0,
        loading: false,
        hasSearched: false,
        resultCount: null,
        searchSeq: 0,

        init() {
            // Load a first page so the table is not empty on open.
            this.search();
        },

        selectedCountLabel() {
            if (this.selectAll) return this.labels.allMatching || 'All matching';
            return String(this.selected.length);
        },

        isSelected(id) {
            return this.selected.includes(String(id));
        },

        togglePerson(id, checked) {
            const key = String(id);
            this.selectAll = false;
            if (checked) {
                if (!this.selected.includes(key)) this.selected.push(key);
            } else {
                this.selected = this.selected.filter((x) => x !== key);
            }
        },

        async search() {
            const seq = ++this.searchSeq;
            this.loading = true;
            this.hasSearched = true;
            try {
                const params = new URLSearchParams();
                Object.entries(this.filters).forEach(([k, v]) => {
                    if (v === '' || v === false || v === null || v === undefined) return;
                    params.set(k, v === true ? '1' : String(v));
                });
                params.set('limit', '100');
                const res = await fetch(this.searchUrl + '?' + params.toString(), {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    cache: 'no-store',
                });
                const data = await res.json();
                if (seq !== this.searchSeq) return;
                this.people = data.people || [];
                this.resultCount = typeof data.count === 'number' ? data.count : this.people.length;
            } catch (e) {
                if (seq !== this.searchSeq) return;
                this.people = [];
                this.resultCount = 0;
            } finally {
                if (seq === this.searchSeq) this.loading = false;
            }
        },

        selectVisible() {
            const ids = this.people.map((p) => String(p.person_id));
            this.selected = Array.from(new Set([...this.selected, ...ids]));
            this.selectAll = false;
        },

        clearSelected() {
            this.selected = [];
            this.selectAll = false;
        },

        onTemplateKey(e) {
            if (e.key === '{') {
                this.showVars = true;
            } else if (e.key === 'Escape') {
                this.showVars = false;
            }
        },

        insertVar(v) {
            const el = this.$refs.template;
            const start = el.selectionStart;
            const end = el.selectionEnd;
            const token = '{' + v + '}';
            let before = this.template.slice(0, start);
            let after = this.template.slice(end);
            if (before.endsWith('{')) before = before.slice(0, -1);
            this.template = before + token + after;
            this.showVars = false;
            this.$nextTick(() => {
                el.focus();
                const pos = before.length + token.length;
                el.setSelectionRange(pos, pos);
            });
        },

        async loadPreview() {
            const ids = this.selectAll
                ? this.people.map((p) => p.person_id)
                : this.selected.map(Number);
            if (!ids.length || !this.template) return;
            const res = await fetch(this.previewUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.csrf,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({
                    message_template: this.template,
                    person_ids: ids.slice(0, 50),
                    missing_variable_behavior: this.$refs.missingBehavior?.value || 'fallback',
                    fallback_name: this.$refs.fallbackName?.value || this.labels.friend || 'Our friend',
                }),
            });
            const data = await res.json();
            this.previews = data.previews || [];
            this.estimated = data.estimated || 0;
            this.previewIndex = 0;
        },

        currentPreview() {
            return this.previews[this.previewIndex] || null;
        },

        nextPreview() {
            if (this.previewIndex < this.previews.length - 1) this.previewIndex++;
        },

        prevPreview() {
            if (this.previewIndex > 0) this.previewIndex--;
        },

        prepareSubmit() {
            // Hidden person_ids[] inputs sync via Alpine template.
        },
    };
}
</script>
