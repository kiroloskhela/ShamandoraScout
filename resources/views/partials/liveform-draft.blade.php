{{--
    Keeps a public liveform's typed answers in this browser so a refresh,
    a closed tab, or a failed submit does not wipe the application.
    Files are not stored. Server old() input wins over the saved draft.
--}}
@php
    $skipNames = array_values($skip ?? []);
    $oldInput = collect(session()->getOldInput() ?? [])
        ->reject(fn ($value, $key) => $key === '_token' || is_array($value))
        ->map(fn ($value) => is_bool($value) ? ($value ? '1' : '') : $value)
        ->all();
@endphp
<script>
(function () {
    var form = document.getElementById(@json($formId));
    if (!form) return;

    var storageKey = @json($storageKey);
    var allKeys = ['shamandora.liveform.step1', 'shamandora.liveform.step2', 'shamandora.liveform.questions'];
    var skip = @json($skipNames);
    var oldInput = @json($oldInput);
    var errorKeys = @json($errors->keys());
    var restoreWizard = @json((bool) ($restoreWizard ?? false));
    var maxAgeMs = 24 * 60 * 60 * 1000;
    var dirty = false;
    var restored = false;

    var stepOf = {
        first_name: 1, second_name: 1, third_name: 1, fourth_name: 1,
        email_input: 1, birthdate_input: 1, input_raqam_qawmy: 1,
        personal_phone_number: 1, inputFacebookLink: 1, inputInstagramLink: 1,
        blood_type_input: 1, allergy_food: 1, allergy_medicine: 1,
        medical_diseases: 1, medical_medications: 1, has_emergency_case: 1,
        emergency_details: 1, joining_year_input: 1, folar_id: 1, profile_image: 1,
        father_phone_number: 2, mother_phone_number: 2, home_phone_number: 2,
        has_whatsapp: 2, building_number: 2, floor_number: 2, appartment_number: 2,
        main_street_name: 2, sub_street_name: 2, nearest_landmark: 2,
        manteqa_id: 2, district_id: 2,
        person_school: 3, school_grad_year: 3, person_faculty: 3,
        person_university: 3, university_grad_year: 3,
        spiritual_father: 3, spiritual_father_church: 3,
        scout_uniform_image: 3, qetaa_id: 3
    };

    function cssEscape(value) {
        if (window.CSS && CSS.escape) return CSS.escape(String(value));
        return String(value).replace(/"/g, '\\"');
    }

    function read() {
        try {
            var raw = localStorage.getItem(storageKey);
            if (!raw) return null;
            var data = JSON.parse(raw);
            if (!data || typeof data !== 'object' || !data.fields) return null;
            if (data.savedAt && (Date.now() - data.savedAt) > maxAgeMs) {
                localStorage.removeItem(storageKey);
                return null;
            }
            return data;
        } catch (e) {
            return null;
        }
    }

    function write(data) {
        try {
            localStorage.setItem(storageKey, JSON.stringify(data));
        } catch (e) {}
    }

    function clearAll() {
        try {
            allKeys.forEach(function (key) { localStorage.removeItem(key); });
        } catch (e) {}
    }

    function shouldSkip(el) {
        if (!el.name || el.name === '_token' || el.name === '_method') return true;
        if (el.type === 'file' || el.type === 'password' || el.type === 'submit' || el.type === 'button') return true;
        if (el.readOnly || el.disabled) return true;
        if (el.type === 'hidden' && el.name !== 'allergy_food') return true;
        return skip.indexOf(el.name) !== -1;
    }

    function fieldsNamed(name) {
        return form.querySelectorAll('[name="' + cssEscape(name) + '"]');
    }

    function collect() {
        var fields = {};
        var seenRadio = {};
        form.querySelectorAll('input[name], select[name], textarea[name]').forEach(function (el) {
            if (shouldSkip(el)) return;
            if (el.type === 'radio') {
                if (seenRadio[el.name]) return;
                seenRadio[el.name] = true;
                var checked = form.querySelector('input[type="radio"][name="' + cssEscape(el.name) + '"]:checked');
                fields[el.name] = checked ? checked.value : '';
                return;
            }
            if (el.type === 'checkbox') {
                fields[el.name] = el.checked ? (el.value || '1') : '';
                return;
            }
            fields[el.name] = el.value;
        });
        return fields;
    }

    function hasUserAnswers(fields) {
        return Object.keys(fields || {}).some(function (name) {
            if (name === 'joining_year_input' || name === 'gender' || name === 'sana_marhala_id') return false;
            if (name === 'has_allergy' || name === 'has_medical_history' || name === 'has_emergency_case') {
                return String(fields[name]) === '1';
            }
            return String(fields[name] || '').trim() !== '';
        });
    }

    function isBlank(el) {
        if (el.type === 'checkbox' || el.type === 'radio') return !el.checked;
        return String(el.value || '').trim() === '';
    }

    function applyToField(el, value) {
        if (value == null || Array.isArray(value)) return;
        if (el.type === 'checkbox') {
            var off = value === '' || value === false || value === 0 || value === '0';
            el.checked = off ? false : (value === true || value === 1 || value === '1' || String(value) === String(el.value));
            return;
        }
        if (el.type === 'radio') {
            el.checked = String(el.value) === String(value);
            return;
        }
        if (el.tagName === 'SELECT') {
            var exists = Array.prototype.some.call(el.options, function (option) {
                return option.value === String(value);
            });
            if (!exists) return;
        }
        el.value = String(value);
    }

    function applyMap(values, force) {
        Object.keys(values || {}).forEach(function (name) {
            if (skip.indexOf(name) !== -1) return;
            var nodes = fieldsNamed(name);
            if (!nodes.length || nodes[0].type === 'file') return;
            var first = nodes[0];
            if (!force && first.tagName !== 'SELECT' && first.type !== 'checkbox' && first.type !== 'radio' && !isBlank(first)) {
                return;
            }
            Array.prototype.forEach.call(nodes, function (el) {
                applyToField(el, values[name]);
            });
        });
    }

    function uncheckMissing(values) {
        form.querySelectorAll('input[type="checkbox"][name]').forEach(function (el) {
            if (shouldSkip(el)) return;
            if (!Object.prototype.hasOwnProperty.call(values, el.name)) el.checked = false;
        });
    }

    function syncAllergy() {
        var hidden = document.getElementById('allergy_food');
        var select = document.getElementById('allergy_food_select');
        var other = document.getElementById('allergy_food_other');
        if (!hidden || !select || hidden.form !== form) return;

        var value = hidden.value || '';
        var known = Array.prototype.some.call(select.options, function (option) {
            return option.value === value;
        });

        if (!value) {
            select.value = '';
        } else if (known && value !== 'أخرى') {
            select.value = value;
        } else {
            select.value = 'أخرى';
            if (other) other.value = value === 'أخرى' ? (other.value || '') : value;
        }

        select.dispatchEvent(new Event('change', { bubbles: true }));
    }

    function wizardStepFromErrors() {
        var step = null;
        errorKeys.forEach(function (key) {
            if (!stepOf[key]) return;
            step = step == null ? stepOf[key] : Math.min(step, stepOf[key]);
        });
        return step;
    }

    function currentWizardStep() {
        var root = document.getElementById('wizardRoot');
        try {
            if (root && window.Alpine) return Alpine.$data(root).step;
        } catch (e) {}
        return window.__liveformWizardStep || null;
    }

    function save() {
        if (!dirty) return;
        var data = {
            savedAt: Date.now(),
            wizardStep: currentWizardStep(),
            fields: collect()
        };
        write(data);
    }

    var timer = null;
    function schedule() {
        dirty = true;
        clearTimeout(timer);
        timer = setTimeout(save, 250);
    }

    function showBanner() {
        var bar = document.createElement('div');
        bar.setAttribute('data-liveform-draft-banner', '1');
        bar.className = 'mb-6 rounded-2xl border border-teal-200 bg-teal-50 px-4 py-3 text-sm leading-relaxed text-teal-950';

        var text = document.createElement('p');
        text.textContent = @json(__('We restored your saved answers on this device.'));
        bar.appendChild(text);
        @if ($mentionPhotos ?? false)
        var photos = document.createElement('p');
        photos.className = 'mt-1';
        photos.textContent = @json(__('Choose the photos again if you had uploaded any.'));
        bar.appendChild(photos);
        @endif

        var button = document.createElement('button');
        button.type = 'button';
        button.className = 'mt-2 font-bold text-brand-800 underline';
        button.textContent = @json(__('Start fresh'));
        button.addEventListener('click', function () {
            clearAll();
            window.location.href = @json(route('person.liveform-create'));
        });
        bar.appendChild(button);
        form.insertBefore(bar, form.firstChild);
    }

    var draft = read();
    if (draft && hasUserAnswers(draft.fields)) {
        applyMap(draft.fields, true);
        restored = true;
    }

    var hasOld = oldInput && Object.keys(oldInput).length > 0;
    if (hasOld) {
        applyMap(oldInput, true);
        uncheckMissing(oldInput);
    }

    if (restored || hasOld) {
        syncAllergy();
        var stage = document.getElementById('sana_marhala_id');
        if (stage) stage.dispatchEvent(new Event('change', { bubbles: true }));
        var emergency = document.getElementById('has_emergency_case');
        if (emergency && emergency.form === form) emergency.dispatchEvent(new Event('change', { bubbles: true }));
        if (restored && !hasOld) showBanner();
        document.dispatchEvent(new CustomEvent('liveform:restored'));

        @if ($enableSubmit ?? false)
        var requiredMissing = false;
        form.querySelectorAll('[required]').forEach(function (el) {
            if (!String(el.value || '').trim()) requiredMissing = true;
        });
        var submit = form.querySelector('[type="submit"]');
        if (submit && !requiredMissing) submit.disabled = false;
        @endif
    }

    if (restoreWizard) {
        var preferred = wizardStepFromErrors() || (draft && draft.wizardStep) || 1;
        window.__liveformWizardStep = preferred;
        document.addEventListener('alpine:initialized', function () {
            var root = document.getElementById('wizardRoot');
            if (!root || !window.Alpine || !Alpine.effect) return;
            Alpine.effect(function () {
                var step = Alpine.$data(root).step;
                var existing = read();
                if (!existing && !dirty) return;
                var data = existing || { fields: collect() };
                data.savedAt = Date.now();
                data.wizardStep = step;
                data.fields = collect();
                write(data);
            });
        });
    }

    form.addEventListener('input', schedule);
    form.addEventListener('change', schedule);
    form.addEventListener('submit', function () {
        dirty = true;
        save();
    }, true);
    window.addEventListener('pagehide', save);
})();
</script>
