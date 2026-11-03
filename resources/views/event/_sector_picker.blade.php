@php
    $posted = old('qetaa_form');
    $hostIds = array_map('intval', (array) ($posted ? old('qetaa_id', []) : ($selectedQetaat ?? [])));
    $scopes = (array) ($posted ? old('qetaa_scope', []) : ($selectedScopes ?? []));
    $frozen = \App\Domain\Enrolment\LiveFormQetaaResolver::FROZEN_QETAA_IDS;
    $specialOrder = [
        \App\Domain\Enrolment\LiveFormQetaaResolver::QETAA_QADA,
        \App\Domain\Enrolment\LiveFormQetaaResolver::QETAA_JAWWALA,
        \App\Domain\Enrolment\LiveFormQetaaResolver::QETAA_EADAD_QADA,
    ];
    $normalQetaat = $qetaat->filter(fn ($qetaa) => ! in_array((int) $qetaa->QetaaID, $frozen, true))->values();
    $specialQetaat = collect($specialOrder)
        ->map(fn ($id) => $qetaat->first(fn ($qetaa) => (int) $qetaa->QetaaID === $id))
        ->filter()
        ->values();
    $hostsOpen = $hostIds !== [];
    $tone = $tone ?? 'blue';
    $boxClass = $tone === 'emerald'
        ? 'text-emerald-500 focus:ring-emerald-500'
        : 'text-blue-600 focus:ring-blue-500';
    $activeClass = $tone === 'emerald'
        ? 'bg-emerald-50 border-l-4 border-emerald-500'
        : 'bg-blue-50 border-l-4 border-blue-500';
    $hoverClass = $tone === 'emerald' ? 'hover:bg-emerald-50' : 'hover:bg-slate-50';
@endphp

<input type="hidden" name="qetaa_form" value="1">

<div id="qetaa-picker" class="border rounded-lg border-slate-200 p-4 bg-white min-h-32 max-h-96 overflow-y-auto" data-active-class="{{ $activeClass }}">
    @foreach ($normalQetaat as $qetaa)
        @php $checked = in_array((int) $qetaa->QetaaID, $hostIds, true); @endphp
        <label class="qetaa-option flex items-center mb-2 cursor-pointer {{ $hoverClass }} p-2 rounded {{ $checked ? $activeClass : '' }}"
            style="font-family: 'Cairo', sans-serif; direction: rtl;">
            <input type="checkbox" name="qetaa_id[]" value="{{ $qetaa->QetaaID }}"
                data-qetaa-name="{{ $qetaa->QetaaName }}"
                class="qetaa-host ml-2 w-4 h-4 {{ $boxClass }} bg-gray-100 border-gray-300 rounded focus:ring-2"
                @checked($checked)>
            <span class="text-sm text-slate-700">{{ $qetaa->QetaaName }}</span>
        </label>
    @endforeach

    @foreach ($specialQetaat as $qetaa)
        @php
            $scope = $scopes[(int) $qetaa->QetaaID] ?? $scopes[(string) $qetaa->QetaaID] ?? null;
            $servingName = __(':name who serve the selected sectors', ['name' => $qetaa->QetaaName]);
            $allName = __('All :name', ['name' => $qetaa->QetaaName]);
        @endphp
        <div class="mt-3 border-t border-slate-100 pt-3" data-scope-group="{{ $qetaa->QetaaID }}">
            <label class="qetaa-option flex items-center mb-2 p-2 rounded {{ $hoverClass }} {{ $scope === 'serving' ? $activeClass : '' }} {{ $hostsOpen ? 'cursor-pointer' : 'opacity-40' }}"
                style="font-family: 'Cairo', sans-serif; direction: rtl;">
                <input type="checkbox" name="qetaa_scope[{{ $qetaa->QetaaID }}]" value="serving"
                    data-qetaa-name="{{ $servingName }}"
                    class="qetaa-scope qetaa-scope-serving ml-2 w-4 h-4 {{ $boxClass }} bg-gray-100 border-gray-300 rounded focus:ring-2"
                    @checked($scope === 'serving') @disabled(! $hostsOpen)>
                <span class="text-sm text-slate-700">{{ $servingName }}</span>
            </label>
            <label class="qetaa-option flex items-center mb-2 cursor-pointer {{ $hoverClass }} p-2 rounded {{ $scope === 'all' ? $activeClass : '' }}"
                style="font-family: 'Cairo', sans-serif; direction: rtl;">
                <input type="checkbox" name="qetaa_scope[{{ $qetaa->QetaaID }}]" value="all"
                    data-qetaa-name="{{ $allName }}"
                    class="qetaa-scope qetaa-scope-all ml-2 w-4 h-4 {{ $boxClass }} bg-gray-100 border-gray-300 rounded focus:ring-2"
                    @checked($scope === 'all')>
                <span class="text-sm text-slate-700">{{ $allName }}</span>
            </label>
        </div>
    @endforeach
</div>

@once
    @push('scripts')
        <script>
            (function () {
                const picker = document.getElementById('qetaa-picker');
                if (!picker) return;

                function syncServingSectorOptions() {
                    const open = picker.querySelectorAll('.qetaa-host:checked').length > 0;
                    picker.querySelectorAll('.qetaa-scope-serving').forEach((box) => {
                        const label = box.closest('label');
                        box.disabled = !open;
                        if (!open) box.checked = false;
                        if (label) label.classList.toggle('opacity-40', !open);
                        if (label) label.classList.toggle('cursor-pointer', open);
                    });
                }

                picker.querySelectorAll('[data-scope-group]').forEach((group) => {
                    group.querySelectorAll('.qetaa-scope').forEach((box) => {
                        box.addEventListener('change', () => {
                            if (!box.checked) return;
                            group.querySelectorAll('.qetaa-scope').forEach((other) => {
                                if (other !== box) other.checked = false;
                            });
                        });
                    });
                });

                picker.querySelectorAll('.qetaa-host').forEach((box) => {
                    box.addEventListener('change', syncServingSectorOptions);
                });

                syncServingSectorOptions();
            })();
        </script>
    @endpush
@endonce
