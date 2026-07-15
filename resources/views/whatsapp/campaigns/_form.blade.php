@php
    $isEdit = isset($campaign);
    $action = $isEdit ? route('whatsapp.campaigns.update', $campaign) : route('whatsapp.campaigns.store');
    $method = $isEdit ? 'PUT' : 'POST';
    $selectedIds = $selectedIds ?? [];
@endphp

<form method="POST" action="{{ $action }}" id="campaign-form"
    x-data="waCampaignForm({
        searchUrl: @js(route('whatsapp.campaigns.contacts.search')),
        previewUrl: @js(route('whatsapp.campaigns.preview')),
        csrf: @js(csrf_token()),
        initialSelected: @js($selectedIds),
        variables: @js($variables),
        highCount: {{ (int) $highCountThreshold }}
    })" class="space-y-6" dir="rtl">
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <div class="bg-white rounded-lg shadow border border-gray-100 p-6 space-y-4">
        <h2 class="text-lg font-bold text-gray-800">1) بيانات الحملة</h2>
        <div>
            <label class="block text-sm text-gray-600 mb-1">اسم الحملة</label>
            <input type="text" name="name" required value="{{ old('name', $campaign->name ?? '') }}"
                class="w-full border border-gray-300 rounded-lg px-3 py-2">
        </div>
        <div class="grid md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm text-gray-600 mb-1">أقل تأخير (ث)</label>
                <input type="number" name="min_delay_seconds" min="1" max="600"
                    value="{{ old('min_delay_seconds', $campaign->min_delay_seconds ?? 8) }}"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">أقصى تأخير (ث)</label>
                <input type="number" name="max_delay_seconds" min="1" max="600"
                    value="{{ old('max_delay_seconds', $campaign->max_delay_seconds ?? 15) }}"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">حد أقصى / ساعة</label>
                <input type="number" name="max_messages_per_hour" min="1" max="500"
                    value="{{ old('max_messages_per_hour', $campaign->max_messages_per_hour ?? 60) }}"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2">
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow border border-gray-100 p-6 space-y-4">
        <h2 class="text-lg font-bold text-gray-800">2) اختيار المستلمين</h2>
        <div class="grid md:grid-cols-3 gap-3">
            <input type="text" x-model="filters.q" name="filter_q" placeholder="بحث بالاسم / الهاتف / الكود"
                class="border border-gray-300 rounded-lg px-3 py-2 md:col-span-2">
            <select x-model="filters.gender" name="filter_gender" class="border border-gray-300 rounded-lg px-3 py-2">
                <option value="">الجنس (الكل)</option>
                <option value="Male">ذكر</option>
                <option value="Female">أنثى</option>
            </select>
            <select x-model="filters.qetaa_id" name="filter_qetaa_id" class="border border-gray-300 rounded-lg px-3 py-2">
                <option value="">القطاع</option>
                @foreach ($qetaat as $q)
                    <option value="{{ $q->QetaaID }}">{{ $q->QetaaName }}</option>
                @endforeach
            </select>
            <select x-model="filters.group_id" name="filter_group_id" class="border border-gray-300 rounded-lg px-3 py-2">
                <option value="">المجموعة / الخدمة</option>
                @foreach ($groups as $g)
                    <option value="{{ $g->GroupID }}">{{ $g->GroupName }}</option>
                @endforeach
            </select>
            <select x-model="filters.manteqa_id" name="filter_manteqa_id" class="border border-gray-300 rounded-lg px-3 py-2">
                <option value="">المنطقة</option>
                @foreach ($manteqat as $m)
                    <option value="{{ $m->ManteqaID }}">{{ $m->ManteqaName }}</option>
                @endforeach
            </select>
            <select x-model="filters.district_id" name="filter_district_id" class="border border-gray-300 rounded-lg px-3 py-2">
                <option value="">الحي</option>
                @foreach ($districts as $d)
                    <option value="{{ $d->DistrictID }}">{{ $d->DistrictName }}</option>
                @endforeach
            </select>
            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" name="filter_has_whatsapp" value="1" x-model="filters.has_whatsapp">
                لديه واتساب فقط
            </label>
        </div>
        <div class="flex flex-wrap gap-2">
            <button type="button" @click="search()" class="bg-gray-800 text-white px-4 py-2 rounded-lg text-sm">بحث</button>
            <button type="button" @click="selectVisible()" class="bg-emerald-600 text-white px-4 py-2 rounded-lg text-sm">تحديد الظاهر</button>
            <label class="flex items-center gap-2 text-sm border rounded-lg px-3 py-2">
                <input type="checkbox" name="select_all" value="1" x-model="selectAll">
                تحديد كل المطابقين للفلاتر (حتى 2000)
            </label>
            <span class="text-sm text-gray-600 self-center">المحدد: <strong x-text="selectedCount()"></strong></span>
        </div>

        <div class="max-h-80 overflow-auto border rounded-lg">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 sticky top-0">
                    <tr>
                        <th class="px-3 py-2"></th>
                        <th class="px-3 py-2 text-right">الاسم</th>
                        <th class="px-3 py-2 text-right">الهاتف</th>
                        <th class="px-3 py-2 text-right">القطاع</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="p in people" :key="p.person_id">
                        <tr class="border-t">
                            <td class="px-3 py-2">
                                <input type="checkbox" :value="p.person_id" x-model="selected"
                                    name="person_ids[]">
                            </td>
                            <td class="px-3 py-2" x-text="p.full_name"></td>
                            <td class="px-3 py-2 font-mono text-xs" x-text="p.phone"></td>
                            <td class="px-3 py-2" x-text="p.qetaa || '—'"></td>
                        </tr>
                    </template>
                </tbody>
            </table>
            <p class="p-3 text-gray-500 text-sm" x-show="people.length === 0">لا نتائج. اضغط بحث.</p>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow border border-gray-100 p-6 space-y-4 relative">
        <h2 class="text-lg font-bold text-gray-800">3) الرسالة</h2>
        <p class="text-sm text-gray-500">اكتب <code class="bg-gray-100 px-1">{</code> لإدراج متغير. المتاح: {name}</p>
        <div class="relative">
            <textarea name="message_template" x-ref="template" x-model="template" @keydown="onTemplateKey($event)"
                required rows="5"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 font-mono text-sm"
                placeholder="مرحباً {name}، ...">{{ old('message_template', $campaign->message_template ?? '') }}</textarea>
            <div x-show="showVars" x-cloak
                class="absolute z-20 mt-1 bg-white border rounded-lg shadow-lg w-48 overflow-hidden">
                <template x-for="v in variables" :key="v">
                    <button type="button" @click="insertVar(v)"
                        class="block w-full text-right px-3 py-2 text-sm hover:bg-emerald-50"
                        x-text="'{' + v + '}'"></button>
                </template>
            </div>
        </div>
        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm text-gray-600 mb-1">سلوك المتغير الناقص</label>
                <select name="missing_variable_behavior" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                    @foreach (['fallback' => 'قيمة بديلة', 'empty' => 'فارغ', 'skip' => 'تخطّي المستلم', 'warn' => 'تحذير قبل الإرسال'] as $val => $label)
                        <option value="{{ $val }}" @selected(old('missing_variable_behavior', $campaign->missing_variable_behavior ?? 'fallback') === $val)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">الاسم البديل</label>
                <input type="text" name="fallback_name"
                    value="{{ old('fallback_name', $campaign->fallback_name ?? 'صديقنا') }}"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2">
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow border border-gray-100 p-6 space-y-4">
        <div class="flex items-center justify-between gap-3 flex-wrap">
            <h2 class="text-lg font-bold text-gray-800">4) معاينة</h2>
            <button type="button" @click="loadPreview()" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm">تحديث المعاينة</button>
        </div>
        <p class="text-sm text-gray-600">تقدير الرسائل: <strong x-text="estimated"></strong></p>
        <template x-if="previews.length">
            <div class="border rounded-lg p-4 space-y-2">
                <div class="flex items-center justify-between">
                    <button type="button" @click="prevPreview()" class="text-sm px-3 py-1 border rounded">السابق</button>
                    <span class="text-sm" x-text="(previewIndex+1) + ' / ' + previews.length"></span>
                    <button type="button" @click="nextPreview()" class="text-sm px-3 py-1 border rounded">التالي</button>
                </div>
                <div class="text-sm text-gray-500" x-text="currentPreview()?.full_name + ' — ' + currentPreview()?.phone"></div>
                <pre class="whitespace-pre-wrap text-sm bg-gray-50 p-3 rounded" x-text="currentPreview()?.message"></pre>
                <p class="text-amber-700 text-sm" x-show="currentPreview()?.missing?.length"
                    x-text="'متغيرات ناقصة: ' + (currentPreview()?.missing || []).join(', ')"></p>
                <p class="text-red-700 text-sm" x-show="currentPreview()?.skipped">سيتم تخطي هذا المستلم</p>
            </div>
        </template>
    </div>

    <div class="flex gap-3">
        <button type="submit" class="bg-emerald-600 text-white px-6 py-3 rounded-lg font-semibold">حفظ المسودة</button>
        <a href="{{ route('whatsapp.campaigns.index') }}" class="px-6 py-3 rounded-lg border">إلغاء</a>
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
        filters: { q: '', gender: '', qetaa_id: '', group_id: '', manteqa_id: '', district_id: '', has_whatsapp: false },
        people: [],
        selected: (cfg.initialSelected || []).map(String),
        selectAll: false,
        template: @js(old('message_template', $campaign->message_template ?? '')),
        showVars: false,
        previews: [],
        previewIndex: 0,
        estimated: 0,
        selectedCount() {
            return this.selectAll ? (this.people.length || 'الكل المطابق') : this.selected.length;
        },
        async search() {
            const params = new URLSearchParams();
            Object.entries(this.filters).forEach(([k, v]) => {
                if (v === '' || v === false || v === null) return;
                params.set(k === 'q' ? 'q' : k, v === true ? '1' : v);
            });
            params.set('limit', '100');
            const res = await fetch(this.searchUrl + '?' + params.toString(), { headers: { 'Accept': 'application/json' } });
            const data = await res.json();
            this.people = data.people || [];
        },
        selectVisible() {
            const ids = this.people.map(p => String(p.person_id));
            this.selected = Array.from(new Set([...this.selected, ...ids]));
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
            // If user just typed '{', replace trailing {
            let before = this.template.slice(0, start);
            let after = this.template.slice(end);
            if (before.endsWith('{')) {
                before = before.slice(0, -1);
            }
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
                ? this.people.map(p => p.person_id)
                : this.selected.map(Number);
            if (!ids.length || !this.template) return;
            const res = await fetch(this.previewUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.csrf,
                },
                body: JSON.stringify({
                    message_template: this.template,
                    person_ids: ids.slice(0, 50),
                    missing_variable_behavior: document.querySelector('[name=missing_variable_behavior]')?.value || 'fallback',
                    fallback_name: document.querySelector('[name=fallback_name]')?.value || 'صديقنا',
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
    };
}
</script>
