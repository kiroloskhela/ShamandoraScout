@extends('layouts.app')

@section('title', __('Family tree'))

@section('content')
    <style>
        :root {
            --bg: #f5f7fa;
            --card: #ffffff;
            --ink: #111827;
            --muted: #6b7280;
            --border: rgba(0, 0, 0, 0.08);
            --blue: #2563eb;
            --blue-bg: #eff6ff;
            --pink: #db2777;
            --pink-bg: #fdf2f8;
            --sky: #0284c7;
            --sky-bg: #f0f9ff;
            --rose: #e11d48;
            --rose-bg: #fff1f2;
            --green: #16a34a;
            --green-bg: #f0fdf4;
            --amber: #d97706;
            --amber-bg: #fffbeb;
            --slate: #475569;
            --slate-bg: #f8fafc;
            --cyan: #0891b2;
            --cyan-bg: #ecfeff;
            --purple: #7c3aed;
            --purple-bg: #faf5ff;
            --teal: #0d9488;
            --teal-bg: #f0fdfa;
            --yellow: #ca8a04;
            --yellow-bg: #fefce8;
            --lime: #65a30d;
            --lime-bg: #f7fee7;
            --radius: 16px;
            --shadow: 0 4px 16px rgba(0, 0, 0, 0.07);
        }

        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Cairo', sans-serif;
            background: var(--bg);
        }

        /* ─── PAGE ─── */
        .ft-page {
            min-height: 100vh;
            padding: 28px 16px 80px;
            background:
                radial-gradient(ellipse 70% 50% at 15% 0%, rgba(37, 99, 235, 0.07) 0%, transparent 60%),
                radial-gradient(ellipse 50% 40% at 90% 5%, rgba(124, 58, 237, 0.05) 0%, transparent 50%),
                #f5f7fa;
        }

        .ft-wrap {
            max-width: 1100px;
            margin: 0 auto;
        }

        /* ─── SELECTOR ─── */
        .ft-selector {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 24px;
            padding: 24px 28px;
            box-shadow: var(--shadow);
            margin-bottom: 40px;
            display: flex;
            gap: 14px;
            align-items: center;
            flex-wrap: wrap;
        }

        .ft-select-wrap {
            flex: 1;
            min-width: 240px;
            position: relative;
        }

        .ft-select-wrap::after {
            content: "▾";
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--muted);
            pointer-events: none;
            font-size: 13px;
        }

        .ft-select {
            width: 100%;
            appearance: none;
            background: #f8fafc;
            border: 1.5px solid #e2e8f0;
            border-radius: 14px;
            padding: 12px 16px 12px 40px;
            font-family: 'Cairo', sans-serif;
            font-size: 14px;
            font-weight: 600;
            color: var(--ink);
            cursor: pointer;
            outline: none;
            transition: border-color .2s, box-shadow .2s;
        }

        .ft-select:focus {
            border-color: var(--blue);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        }

        .ft-title-group {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .ft-tree-icon {
            font-size: 28px;
        }

        .ft-title {
            font-size: 20px;
            font-weight: 900;
            color: var(--ink);
        }

        .ft-btn {
            border: 0;
            border-radius: 14px;
            padding: 12px 24px;
            font-family: 'Cairo', sans-serif;
            font-size: 14px;
            font-weight: 800;
            cursor: pointer;
            transition: all .2s;
            background: linear-gradient(135deg, #1d4ed8, #3b82f6);
            color: #fff;
            box-shadow: 0 4px 16px rgba(37, 99, 235, 0.3);
            white-space: nowrap;
        }

        .ft-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 24px rgba(37, 99, 235, 0.35);
        }

        /* ─── TREE CANVAS ─── */
        .ft-tree {
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0;
        }

        /* ─── TIERS ─── */
        .ft-tier {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 14px;
            width: 100%;
            position: relative;
            z-index: 2;
        }

        /* CONNECTOR LINES */
        .ft-connector-wrap {
            width: 100%;
            display: flex;
            justify-content: center;
            position: relative;
            z-index: 1;
        }

        .ft-vline {
            width: 2px;
            background: linear-gradient(to bottom, #cbd5e1, #e2e8f0);
            border-radius: 2px;
            margin: 0 auto;
        }

        .ft-hbar-wrap {
            width: 100%;
            display: flex;
            justify-content: center;
            position: relative;
        }

        .ft-hbar {
            height: 2px;
            background: #e2e8f0;
            border-radius: 2px;
            position: relative;
        }

        .ft-hbar::before,
        .ft-hbar::after {
            content: '';
            position: absolute;
            top: -1px;
            width: 2px;
            background: #cbd5e1;
        }

        .ft-hbar::before {
            right: 0;
        }

        .ft-hbar::after {
            left: 0;
        }

        /* ─── PERSON NODES ─── */
        .ft-node {
            position: relative;
            background: var(--card);
            border: 1.5px solid var(--border);
            border-radius: var(--radius);
            padding: 14px 16px;
            min-width: 155px;
            max-width: 200px;
            text-align: center;
            box-shadow: var(--shadow);
            transition: transform .25s cubic-bezier(.16, 1, .3, 1), box-shadow .25s;
            cursor: default;
            overflow: hidden;
        }

        .ft-node:hover {
            transform: translateY(-5px) scale(1.02);
            box-shadow: 0 12px 32px rgba(0, 0, 0, 0.12);
            z-index: 10;
        }

        /* Top color bar */
        .ft-node-bar {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            border-radius: var(--radius) var(--radius) 0 0;
        }

        .ft-node-emoji {
            font-size: 22px;
            margin-bottom: 6px;
            display: block;
        }

        .ft-node-name {
            font-size: 13.5px;
            font-weight: 800;
            color: var(--ink);
            line-height: 1.3;
            margin-bottom: 4px;
        }

        .ft-node-id {
            font-size: 11px;
            color: var(--muted);
            direction: ltr;
            margin-bottom: 6px;
        }

        .ft-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 800;
            border: 1px solid transparent;
        }

        /* Color variants */
        .node-center {
            background: linear-gradient(135deg, #1e3a8a, #2563eb);
            border-color: transparent;
            color: #fff;
            min-width: 200px;
            max-width: 260px;
            box-shadow: 0 16px 48px rgba(29, 78, 216, 0.3);
        }

        .node-center .ft-node-name {
            color: #fff;
            font-size: 17px;
        }

        .node-center .ft-node-id {
            color: rgba(255, 255, 255, 0.7);
        }

        .node-father {
            background: var(--blue-bg);
            border-color: #bfdbfe;
        }

        .node-father .ft-node-bar {
            background: var(--blue);
        }

        .node-father .ft-badge {
            background: #dbeafe;
            color: #1d4ed8;
            border-color: #bfdbfe;
        }

        .node-mother {
            background: var(--pink-bg);
            border-color: #fbcfe8;
        }

        .node-mother .ft-node-bar {
            background: var(--pink);
        }

        .node-mother .ft-badge {
            background: #fce7f3;
            color: #9d174d;
            border-color: #fbcfe8;
        }

        .node-husband {
            background: var(--sky-bg);
            border-color: #bae6fd;
        }

        .node-husband .ft-node-bar {
            background: var(--sky);
        }

        .node-husband .ft-badge {
            background: #e0f2fe;
            color: #0369a1;
            border-color: #bae6fd;
        }

        .node-wife {
            background: var(--rose-bg);
            border-color: #fecdd3;
        }

        .node-wife .ft-node-bar {
            background: var(--rose);
        }

        .node-wife .ft-badge {
            background: #ffe4e6;
            color: #be123c;
            border-color: #fecdd3;
        }

        .node-fiance {
            background: var(--green-bg);
            border-color: #bbf7d0;
        }

        .node-fiance .ft-node-bar {
            background: var(--green);
        }

        .node-fiance .ft-badge {
            background: #dcfce7;
            color: #15803d;
            border-color: #bbf7d0;
        }

        .node-fiancee {
            background: var(--amber-bg);
            border-color: #fde68a;
        }

        .node-fiancee .ft-node-bar {
            background: var(--amber);
        }

        .node-fiancee .ft-badge {
            background: #fef9c3;
            color: #92400e;
            border-color: #fde68a;
        }

        .node-sibling {
            background: var(--slate-bg);
            border-color: #e2e8f0;
        }

        .node-sibling .ft-node-bar {
            background: var(--slate);
        }

        .node-sibling .ft-badge {
            background: #f1f5f9;
            color: #475569;
            border-color: #e2e8f0;
        }

        .node-child {
            background: var(--cyan-bg);
            border-color: #a5f3fc;
        }

        .node-child .ft-node-bar {
            background: var(--cyan);
        }

        .node-child .ft-badge {
            background: #cffafe;
            color: #0e7490;
            border-color: #a5f3fc;
        }

        .node-grandparent {
            background: var(--purple-bg);
            border-color: #e9d5ff;
        }

        .node-grandparent .ft-node-bar {
            background: var(--purple);
        }

        .node-grandparent .ft-badge {
            background: #ede9fe;
            color: #5b21b6;
            border-color: #ddd6fe;
        }

        .node-uncle {
            background: var(--teal-bg);
            border-color: #99f6e4;
        }

        .node-uncle .ft-node-bar {
            background: var(--teal);
        }

        .node-uncle .ft-badge {
            background: #ccfbf1;
            color: #0f766e;
            border-color: #99f6e4;
        }

        .node-cousin {
            background: var(--yellow-bg);
            border-color: #fde047;
        }

        .node-cousin .ft-node-bar {
            background: var(--yellow);
        }

        .node-cousin .ft-badge {
            background: #fef9c3;
            color: #854d0e;
            border-color: #fef08a;
        }

        .node-nephew {
            background: var(--lime-bg);
            border-color: #bef264;
        }

        .node-nephew .ft-node-bar {
            background: var(--lime);
        }

        .node-nephew .ft-badge {
            background: #ecfccb;
            color: #3f6212;
            border-color: #bef264;
        }

        /* ─── SECTION LABEL ─── */
        .ft-section-label {
            text-align: center;
            font-size: 11px;
            font-weight: 700;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: 1.2px;
            margin: 0 0 10px;
            opacity: 0.7;
        }

        /* ─── SPACERS ─── */
        .v8 {
            height: 8px;
        }

        .v12 {
            height: 12px;
        }

        .v16 {
            height: 16px;
        }

        .v24 {
            height: 24px;
        }

        .v32 {
            height: 32px;
        }

        .v40 {
            height: 40px;
        }

        /* connector segments */
        .conn-line {
            width: 2px;
            background: #d1d5db;
            border-radius: 2px;
            margin: 0 auto;
        }

        /* Branching connector */
        .ft-branch {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 100%;
        }

        .ft-branch-bar {
            width: 60%;
            height: 2px;
            background: #d1d5db;
            border-radius: 2px;
            position: relative;
        }

        /* ─── WELCOME ─── */
        .ft-welcome {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 28px;
            padding: 64px 40px;
            text-align: center;
            box-shadow: var(--shadow);
        }

        .ft-welcome-emoji {
            font-size: 56px;
            margin-bottom: 16px;
        }

        .ft-welcome h2 {
            font-size: 22px;
            font-weight: 900;
            color: var(--ink);
            margin-bottom: 8px;
        }

        .ft-welcome p {
            font-size: 14px;
            color: var(--muted);
            font-weight: 500;
        }

        /* ─── ANIMATIONS ─── */
        @keyframes fadeDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: scale(0.92);
            }

            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        @keyframes drawLine {
            from {
                transform: scaleY(0);
                transform-origin: top;
            }

            to {
                transform: scaleY(1);
                transform-origin: top;
            }
        }

        .anim-down {
            animation: fadeDown .55s cubic-bezier(.16, 1, .3, 1) both;
        }

        .anim-up {
            animation: fadeUp .55s cubic-bezier(.16, 1, .3, 1) both;
        }

        .anim-in {
            animation: fadeIn .5s cubic-bezier(.16, 1, .3, 1) both;
        }

        .anim-line {
            animation: drawLine .5s cubic-bezier(.16, 1, .3, 1) both;
        }

        .delay-1 {
            animation-delay: .08s;
        }

        .delay-2 {
            animation-delay: .16s;
        }

        .delay-3 {
            animation-delay: .24s;
        }

        .delay-4 {
            animation-delay: .32s;
        }

        .delay-5 {
            animation-delay: .40s;
        }

        .delay-6 {
            animation-delay: .48s;
        }

        .delay-7 {
            animation-delay: .56s;
        }

        .delay-8 {
            animation-delay: .64s;
        }

        /* Pulse on center node */
        @keyframes centerPulse {

            0%,
            100% {
                box-shadow: 0 16px 48px rgba(29, 78, 216, 0.30);
            }

            50% {
                box-shadow: 0 20px 60px rgba(37, 99, 235, 0.45);
            }
        }

        .node-center {
            animation: centerPulse 3s ease-in-out infinite;
        }

        @media (max-width: 700px) {
            .ft-selector {
                flex-direction: column;
                align-items: stretch;
            }

            .ft-title-group {
                justify-content: center;
            }

            .ft-node {
                min-width: 120px;
                max-width: 160px;
            }

            .ft-tier {
                gap: 8px;
            }
        }
    </style>

    @php
        $tree = $tree ?? null;

        $father = $tree['father'] ?? null;
        $mother = $tree['mother'] ?? null;

        $siblings = $tree['siblings'] ?? [];
        $children = $tree['children'] ?? [];

        $wives = $tree['wives'] ?? [];
        $husbands = $tree['husbands'] ?? [];
        $fiancees = $tree['fiancees'] ?? [];
        $fiances = $tree['fiances'] ?? [];

        $directGrandfathers = $tree['direct_grandfathers'] ?? [];
        $directGrandmothers = $tree['direct_grandmothers'] ?? [];
        $paternalGrandfather = $tree['paternal_grandfather'] ?? null;
        $paternalGrandmother = $tree['paternal_grandmother'] ?? null;
        $maternalGrandfather = $tree['maternal_grandfather'] ?? null;
        $maternalGrandmother = $tree['maternal_grandmother'] ?? null;

        $directUnclesAunts = $tree['direct_uncles_aunts'] ?? [];
        $allUnclesAunts = $tree['all_uncles_aunts'] ?? [];

        $paternalCousins = $tree['paternal_cousins'] ?? [];
        $maternalCousins = $tree['maternal_cousins'] ?? [];
        $allCousins = $tree['all_cousins'] ?? [];

        $nephewsNieces = $tree['nephews_nieces'] ?? [];

        $allPartners = array_merge($wives, $husbands, $fiancees, $fiances);

        // Deduplicate grandparents
        $allGrandparents = array_merge($directGrandfathers, $directGrandmothers);
        foreach ([$paternalGrandfather, $paternalGrandmother, $maternalGrandfather, $maternalGrandmother] as $gp) {
            if ($gp) {
                $exists = false;
                foreach ($allGrandparents as $ag) {
                    if (($ag['RaqamQawmy'] ?? '') && ($ag['RaqamQawmy'] ?? '') === ($gp['RaqamQawmy'] ?? '')) {
                        $exists = true;
                        break;
                    }
                }
                if (!$exists) {
                    $allGrandparents[] = $gp;
                }
            }
        }

        $hasGrandparents = count($allGrandparents) > 0;
        $hasParents = $father || $mother;
        $hasPartners = count($allPartners) > 0;
        $hasSiblings = count($siblings) > 0;
        $hasChildren = count($children) > 0;
        $hasUnclesAunts = count($allUnclesAunts) > 0;
        $hasCousins = count($allCousins) > 0;
        $hasNephews = count($nephewsNieces) > 0;
    @endphp

    @php
        // Helper to build a node
        function nodeHtml(
            string $cls,
            string $emoji,
            string $name,
            ?string $id,
            string $badge,
            string $animClass = 'anim-in',
            string $delay = '',
        ): string {
            $idHtml = $id ? '<div class="ft-node-id">' . e($id) . '</div>' : '';
            return '<div class="ft-node ' .
                e($cls) .
                ' ' .
                e($animClass) .
                ' ' .
                e($delay) .
                '">
            <div class="ft-node-bar"></div>
            <span class="ft-node-emoji">' .
                $emoji .
                '</span>
            <div class="ft-node-name">' .
                e($name) .
                '</div>
            ' .
                $idHtml .
                '
            <span class="ft-badge">' .
                e($badge) .
                '</span>
        </div>';
        }
    @endphp

    <div class="ft-page">
        <div class="ft-wrap">

            {{-- Selector --}}
            <div class="ft-selector anim-down">
                <div class="ft-title-group">
                    <span class="ft-tree-icon">🌳</span>
                    <span class="ft-title">{{ __('Family tree') }}</span>
                </div>
                @php
                    $selectedPersonName = '';
                    if (request('person_id')) {
                        $selectedPersonObject = collect($persons)->firstWhere('PersonID', request('person_id'));
                        $selectedPersonName = $selectedPersonObject
                            ? $selectedPersonObject->FullName .
                                (!empty($selectedPersonObject->RaqamQawmy)
                                    ? ' • ' . $selectedPersonObject->RaqamQawmy
                                    : '')
                            : '';
                    }
                @endphp

                <form method="GET" action="{{ route('person-tree.index') }}" style="display:contents">
                    <div class="ft-select-wrap" style="position: relative;">
                        <input type="text" id="person_search" autocomplete="off" placeholder="{{ __('Search and choose a person...') }}"
                            value="{{ $selectedPersonName }}" class="ft-select">

                        <input type="hidden" name="person_id" id="person_id" value="{{ request('person_id') }}">

                        <div id="person_results"
                            class="hidden absolute z-50 mt-2 w-full bg-white border border-slate-200 rounded-2xl shadow-lg max-h-72 overflow-y-auto">
                            @foreach ($persons as $person)
                                <div class="person-option px-4 py-3 cursor-pointer hover:bg-blue-50 border-b border-slate-100 last:border-b-0"
                                    data-id="{{ $person->PersonID }}"
                                    data-name="{{ $person->FullName }}{{ !empty($person->RaqamQawmy) ? ' • ' . $person->RaqamQawmy : '' }}">
                                    <div class="text-sm font-bold text-slate-800">
                                        {{ $person->FullName }}
                                    </div>
                                    <div class="text-xs text-slate-500">
                                        ID: {{ $person->PersonID }}
                                        @if (!empty($person->RaqamQawmy))
                                            • {{ $person->RaqamQawmy }}
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <button type="submit" class="ft-btn">{{ __('View tree') }}</button>
                </form>
            </div>

            @if ($selectedPerson && $tree)
                <div class="ft-tree">

                    {{-- ══ TIER 1: GRANDPARENTS ══ --}}
                    @if ($hasGrandparents)
                        <div class="ft-section-label anim-in">{{ __('Grandparents') }}</div>
                        <div class="ft-tier">
                            @foreach ($allGrandparents as $i => $gp)
                                @php
                                    $isGrandfather =
                                        str_contains(strtolower($gp['RelationName'] ?? ''), 'جد') &&
                                        !str_contains($gp['RelationName'] ?? '', 'جدة');
                                @endphp
                                {!! nodeHtml(
                                    'node-grandparent',
                                    $isGrandfather ? '👴' : '👵',
                                    $gp['FullName'],
                                    $gp['RaqamQawmy'] ?? null,
                                    $gp['RelationName'] ?? __('Grandfather/grandmother'),
                                    'anim-down',
                                    'delay-' . min($i + 1, 8),
                                ) !!}
                            @endforeach
                        </div>
                        <div class="conn-line anim-line delay-2" style="height:32px;"></div>
                    @endif

                    {{-- ══ TIER 2: UNCLES/AUNTS (outer row) ══ --}}
                    @if ($hasUnclesAunts)
                        <div class="ft-section-label anim-in delay-2">{{ __('Uncles and aunts') }}</div>
                        <div class="ft-tier">
                            @foreach ($allUnclesAunts as $i => $ua)
                                {!! nodeHtml(
                                    'node-uncle',
                                    '🧔',
                                    $ua['FullName'],
                                    $ua['RaqamQawmy'] ?? null,
                                    $ua['RelationName'] ?? __('Uncle/aunt'),
                                    'anim-down',
                                    'delay-' . min($i + 1, 8),
                                ) !!}
                            @endforeach
                        </div>
                        <div class="conn-line anim-line delay-3" style="height:28px;"></div>
                    @endif

                    {{-- ══ TIER 3: PARENTS (above center) ══ --}}
                    @if ($hasParents)
                        <div class="ft-section-label anim-in delay-3">{{ __('Parents') }}</div>
                        <div class="ft-tier">
                            @if ($father)
                                {!! nodeHtml(
                                    'node-father',
                                    '👨',
                                    $father['FullName'],
                                    $father['RaqamQawmy'] ?? null,
                                    $father['RelationName'] ?? __('Father'),
                                    'anim-down',
                                    'delay-3',
                                ) !!}
                            @endif
                            @if ($mother)
                                {!! nodeHtml(
                                    'node-mother',
                                    '👩',
                                    $mother['FullName'],
                                    $mother['RaqamQawmy'] ?? null,
                                    $mother['RelationName'] ?? __('Mother'),
                                    'anim-down',
                                    'delay-4',
                                ) !!}
                            @endif
                        </div>
                        <div class="conn-line anim-line delay-4" style="height:28px;"></div>
                    @endif

                    {{-- ══ TIER 4: CENTER ROW  (siblings | center | partners) ══ --}}
                    <div class="ft-tier" style="align-items:center; gap: 20px;">

                        {{-- Siblings column --}}
                        @if ($hasSiblings)
                            <div style="display:flex;flex-direction:column;align-items:center;gap:10px;">
                                <div class="ft-section-label anim-in delay-4" style="margin-bottom:0;">{{ __('Siblings') }}</div>
                                @foreach ($siblings as $i => $sib)
                                    {!! nodeHtml(
                                        'node-sibling',
                                        '🧑‍🤝‍🧑',
                                        $sib['FullName'],
                                        $sib['RaqamQawmy'] ?? null,
                                        $sib['RelationName'] ?? __('Brother/sister'),
                                        'anim-up',
                                        'delay-' . min($i + 3, 8),
                                    ) !!}
                                @endforeach
                            </div>
                            {{-- Horizontal connector --}}
                            <div class="conn-line anim-line delay-4" style="width:28px;height:2px;flex-shrink:0;"></div>
                        @endif

                        {{-- CENTER NODE --}}
                        <div class="ft-node node-center anim-in delay-1"
                            style="padding:22px 24px;min-width:200px;text-align:center;">
                            <span class="ft-node-emoji" style="font-size:30px;">🧑</span>
                            <div class="ft-node-name">{{ $tree['person']->FullName ?? __('Unknown') }}</div>
                            @if (!empty($tree['person']->RaqamQawmy))
                                <div class="ft-node-id">{{ $tree['person']->RaqamQawmy }}</div>
                            @endif
                            <span class="ft-badge"
                                style="background:rgba(255,255,255,0.2);color:#fff;border-color:rgba(255,255,255,0.3);">{{ __('Central person') }}</span>
                        </div>

                        {{-- Partners side --}}
                        @if ($hasPartners)
                            <div class="conn-line anim-line delay-4" style="width:28px;height:2px;flex-shrink:0;"></div>
                            <div style="display:flex;flex-direction:column;align-items:center;gap:10px;">
                                <div class="ft-section-label anim-in delay-4" style="margin-bottom:0;">{{ __('Spouse / fiancé(e)') }}</div>
                                @foreach ($wives as $i => $w)
                                    {!! nodeHtml(
                                        'node-wife',
                                        '👰',
                                        $w['FullName'],
                                        $w['RaqamQawmy'] ?? null,
                                        $w['RelationName'] ?? __('Wife'),
                                        'anim-up',
                                        'delay-' . min($i + 3, 8),
                                    ) !!}
                                @endforeach
                                @foreach ($husbands as $i => $h)
                                    {!! nodeHtml(
                                        'node-husband',
                                        '🤵',
                                        $h['FullName'],
                                        $h['RaqamQawmy'] ?? null,
                                        $h['RelationName'] ?? __('Husband'),
                                        'anim-up',
                                        'delay-' . min($i + 4, 8),
                                    ) !!}
                                @endforeach
                                @foreach ($fiancees as $i => $f)
                                    {!! nodeHtml(
                                        'node-fiancee',
                                        '💛',
                                        $f['FullName'],
                                        $f['RaqamQawmy'] ?? null,
                                        $f['RelationName'] ?? __('Fiancée'),
                                        'anim-up',
                                        'delay-' . min($i + 5, 8),
                                    ) !!}
                                @endforeach
                                @foreach ($fiances as $i => $f)
                                    {!! nodeHtml(
                                        'node-fiance',
                                        '💚',
                                        $f['FullName'],
                                        $f['RaqamQawmy'] ?? null,
                                        $f['RelationName'] ?? __('Fiancé'),
                                        'anim-up',
                                        'delay-' . min($i + 5, 8),
                                    ) !!}
                                @endforeach
                            </div>
                        @endif

                    </div>

                    {{-- ══ CONNECTOR DOWN TO CHILDREN ══ --}}
                    @if ($hasChildren || $hasNephews || $hasCousins)
                        <div class="conn-line anim-line delay-5" style="height:28px;"></div>
                    @endif

                    {{-- ══ TIER 5: CHILDREN ══ --}}
                    @if ($hasChildren)
                        <div class="ft-section-label anim-in delay-5">{{ __('Children') }}</div>
                        <div class="ft-tier">
                            @foreach ($children as $i => $c)
                                {!! nodeHtml(
                                    'node-child',
                                    '👶',
                                    $c['FullName'],
                                    $c['RaqamQawmy'] ?? null,
                                    $c['RelationName'] ?? __('Son/daughter'),
                                    'anim-up',
                                    'delay-' . min($i + 5, 8),
                                ) !!}
                            @endforeach
                        </div>
                    @endif

                    {{-- ══ TIER 6: NEPHEWS/NIECES (below siblings) ══ --}}
                    @if ($hasNephews)
                        @if ($hasChildren)
                            <div class="conn-line anim-line delay-6" style="height:20px;"></div>
                        @endif
                        <div class="ft-section-label anim-in delay-6">{{ __('Nieces and nephews') }}</div>
                        <div class="ft-tier">
                            @foreach ($nephewsNieces as $i => $n)
                                {!! nodeHtml(
                                    'node-nephew',
                                    '🌱',
                                    $n['FullName'],
                                    $n['RaqamQawmy'] ?? null,
                                    $n['RelationName'] ?? __('Child of sibling'),
                                    'anim-up',
                                    'delay-' . min($i + 6, 8),
                                ) !!}
                            @endforeach
                        </div>
                    @endif

                    {{-- ══ TIER 7: COUSINS ══ --}}
                    @if ($hasCousins)
                        @if ($hasChildren || $hasNephews)
                            <div class="conn-line anim-line delay-7" style="height:20px;"></div>
                        @endif
                        <div class="ft-section-label anim-in delay-7">{{ __('Cousins') }}</div>
                        <div class="ft-tier">
                            @foreach ($allCousins as $i => $c)
                                {!! nodeHtml(
                                    'node-cousin',
                                    '👫',
                                    $c['FullName'],
                                    $c['RaqamQawmy'] ?? null,
                                    $c['RelationName'] ?? __('Child of uncle/aunt'),
                                    'anim-up',
                                    'delay-' . min($i + 7, 8),
                                ) !!}
                            @endforeach
                        </div>
                    @endif

                </div>{{-- end ft-tree --}}
            @elseif(request()->filled('person_id'))
                <div class="ft-welcome">
                    <div class="ft-welcome-emoji">⚠️</div>
                    <h2>{{ __('No data found') }}</h2>
                    <p>{{ __('Please choose another person from the list.') }}</p>
                </div>
            @else
                <div class="ft-welcome anim-in">
                    <div class="ft-welcome-emoji">🌳</div>
                    <h2>{{ __('Choose a person to view their family tree') }}</h2>
                    <p>{{ __('Choose a person from the list above to view their family relationships here.') }}</p>
                </div>
            @endif

        </div>
    </div>


    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('person_search');
            const hiddenInput = document.getElementById('person_id');
            const resultsBox = document.getElementById('person_results');
            const options = Array.from(document.querySelectorAll('.person-option'));

            if (!searchInput || !hiddenInput || !resultsBox) return;

            function showResults() {
                resultsBox.classList.remove('hidden');
            }

            function hideResults() {
                resultsBox.classList.add('hidden');
            }

            function filterResults() {
                const keyword = searchInput.value.trim().toLowerCase();
                let visibleCount = 0;

                options.forEach(option => {
                    const name = (option.dataset.name || '').toLowerCase();
                    const id = (option.dataset.id || '').toLowerCase();

                    if (keyword === '' || name.includes(keyword) || id.includes(keyword)) {
                        option.style.display = '';
                        visibleCount++;
                    } else {
                        option.style.display = 'none';
                    }
                });

                if (visibleCount > 0) {
                    showResults();
                } else {
                    hideResults();
                }
            }

            options.forEach(option => {
                option.addEventListener('click', function() {
                    searchInput.value = this.dataset.name || '';
                    hiddenInput.value = this.dataset.id || '';
                    hideResults();
                });
            });

            searchInput.addEventListener('focus', function() {
                filterResults();
            });

            searchInput.addEventListener('input', function() {
                hiddenInput.value = '';
                filterResults();
            });

            document.addEventListener('click', function(e) {
                if (!searchInput.contains(e.target) && !resultsBox.contains(e.target)) {
                    hideResults();
                }
            });
        });
    </script>
@endsection
