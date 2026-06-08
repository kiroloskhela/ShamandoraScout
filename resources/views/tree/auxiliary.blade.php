@extends('layouts.app')

@section('content')
    @php
        $totalPeople = $talaea->sum(fn($taleia) => $taleia->people->count());
        $initials = fn($name) => mb_substr($name ?: '؟', 0, 1, 'UTF-8');
        $palette = [
            ['accent' => '#2563eb', 'soft' => '#eff6ff', 'border' => '#bfdbfe', 'avatar' => '#dbeafe'],
            ['accent' => '#16a34a', 'soft' => '#f0fdf4', 'border' => '#bbf7d0', 'avatar' => '#dcfce7'],
            ['accent' => '#d97706', 'soft' => '#fffbeb', 'border' => '#fde68a', 'avatar' => '#fef3c7'],
            ['accent' => '#7c3aed', 'soft' => '#f5f3ff', 'border' => '#ddd6fe', 'avatar' => '#ede9fe'],
            ['accent' => '#0891b2', 'soft' => '#ecfeff', 'border' => '#a5f3fc', 'avatar' => '#cffafe'],
            ['accent' => '#e11d48', 'soft' => '#fff1f2', 'border' => '#fecdd3', 'avatar' => '#ffe4e6'],
        ];
    @endphp

    <div class="aux-root" dir="rtl">
        <header class="aux-header">
            <div>
                <p class="aux-eyebrow">بيانات الفريق</p>
                <h1>عرض الطلائع</h1>
                <p>اختار القطاع ثم الفريق أو الطلائع المباشرة لعرض الأشخاص داخل كل طليعة.</p>
            </div>

            <form method="GET" action="{{ route('qetaa.auxiliary') }}" class="aux-filters">
                <label class="aux-select">
                    <span>القطاع</span>
                    <select name="qetaa" onchange="this.form.submit()">
                        @if ($servedQetaas->count() !== 1)
                            <option value="">اختر القطاع</option>
                        @endif
                        @foreach ($servedQetaas as $qetaa)
                            <option value="{{ $qetaa->QetaaID }}"
                                {{ (string) $qetaa->QetaaID === (string) $selectedQetaaId ? 'selected' : '' }}>
                                {{ $qetaa->QetaaName }}
                            </option>
                        @endforeach
                    </select>
                </label>

                <label class="aux-select {{ !$selectedQetaaId ? 'is-disabled' : '' }}">
                    <span>الفريق</span>
                    <select name="team" onchange="this.form.submit()" {{ !$selectedQetaaId ? 'disabled' : '' }}>
                        <option value="">اختر الفريق</option>
                        @if (($directTalaeaCount ?? 0) > 0)
                            <option value="direct" {{ (string) $selectedTeamId === 'direct' ? 'selected' : '' }}>
                                الطلائع المباشرة
                            </option>
                        @endif
                        @foreach ($teams as $team)
                            <option value="{{ $team->GroupID }}"
                                {{ (string) $team->GroupID === (string) $selectedTeamId ? 'selected' : '' }}>
                                {{ $team->GroupName }}
                            </option>
                        @endforeach
                    </select>
                </label>
            </form>
        </header>

        <section class="aux-stats">
            <div>
                <strong>{{ $servedQetaas->count() }}</strong>
                <span>قطاع متاح</span>
            </div>
            <div>
                <strong>{{ $teams->count() }}</strong>
                <span>فريق</span>
            </div>
            <div>
                <strong>{{ $talaea->count() }}</strong>
                <span>طليعة</span>
            </div>
            <div>
                <strong>{{ $totalPeople }}</strong>
                <span>شخص</span>
            </div>
        </section>

        @if (!$selectedQetaaId)
            <div class="aux-empty">اختار القطاع أولاً لعرض الفرق المتاحة.</div>
        @elseif (!$selectedTeamId)
            <div class="aux-empty">اختار الفريق أو الطلائع المباشرة لعرض الطلائع التابعة له.</div>
        @elseif ($talaea->isEmpty())
            <div class="aux-empty">لا توجد طلايع مسجلة داخل {{ $selectedTeam->GroupName ?? 'هذا الاختيار' }}.</div>
        @else
            <section class="aux-team-band">
                <div>
                    <span>الفريق المختار</span>
                    <strong>{{ $selectedTeam->GroupName }}</strong>
                </div>
                <div class="aux-team-count">{{ $talaea->count() }} طليعة</div>
            </section>

            <section class="aux-talaea">
                @foreach ($talaea as $taleia)
                    @php
                        $colors = $palette[$loop->index % count($palette)];
                    @endphp
                    <article class="aux-taleia-card"
                        style="--aux-accent: {{ $colors['accent'] }}; --aux-soft: {{ $colors['soft'] }}; --aux-border: {{ $colors['border'] }}; --aux-avatar: {{ $colors['avatar'] }};">
                        <div class="aux-taleia-head">
                            <div>
                                <span class="aux-badge">طليعة</span>
                                <h2>{{ $taleia->GroupName }}</h2>
                            </div>
                            <strong>{{ $taleia->people->count() }}</strong>
                        </div>

                        @if ($taleia->people->isEmpty())
                            <p class="aux-card-empty">لا يوجد أشخاص داخل هذه الطليعة.</p>
                        @else
                            <div class="aux-people">
                                @foreach ($taleia->people as $person)
                                    @php
                                        $imagePath = $person->PersonSystemImagePath ?? null;
                                        $imageSrc = $imagePath
                                            ? (\Illuminate\Support\Str::startsWith($imagePath, ['http://', 'https://', '/'])
                                                ? $imagePath
                                                : asset($imagePath))
                                            : null;
                                    @endphp
                                    <div class="aux-person">
                                        <div class="aux-avatar">
                                            @if ($imageSrc)
                                                <img src="{{ $imageSrc }}" alt="{{ $person->FirstName }} {{ $person->SecondName }}">
                                            @else
                                                {{ $initials($person->FirstName ?? '') }}
                                            @endif
                                        </div>
                                        <div class="aux-person-info">
                                            <span>{{ $person->FirstName }} {{ $person->SecondName }}</span>
                                            @if ($person->ShamandoraCode)
                                                <small>{{ $person->ShamandoraCode }}</small>
                                            @endif
                                        </div>
                                        @if ($person->RotbaName)
                                            <em>{{ $person->RotbaName }}</em>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </article>
                @endforeach
            </section>
        @endif
    </div>

    <style>
        .aux-root {
            min-height: 100vh;
            padding: 24px 20px 48px;
            background: #f5f7fb;
            color: #172033;
            font-family: 'IBM Plex Sans Arabic', system-ui, sans-serif;
        }

        .aux-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            gap: 18px;
            margin-bottom: 16px;
            flex-wrap: wrap;
        }

        .aux-eyebrow {
            color: #2563eb;
            font-size: 12px;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .aux-header h1 {
            font-size: 24px;
            font-weight: 700;
            margin: 0 0 4px;
            letter-spacing: 0;
        }

        .aux-header p {
            color: #64748b;
            font-size: 13px;
            margin: 0;
        }

        .aux-filters {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .aux-select {
            display: flex;
            align-items: center;
            gap: 8px;
            height: 42px;
            padding: 0 12px;
            background: #fff;
            border: 1px solid #dbe3ef;
            border-radius: 8px;
            box-shadow: 0 6px 18px rgba(15, 23, 42, .06);
        }

        .aux-select span {
            color: #64748b;
            font-size: 12px;
            white-space: nowrap;
        }

        .aux-select select {
            min-width: 150px;
            border: 0;
            outline: 0;
            background: transparent;
            color: #172033;
            font-family: inherit;
            font-size: 13px;
        }

        .aux-select.is-disabled {
            opacity: .6;
        }

        .aux-stats {
            display: grid;
            grid-template-columns: repeat(4, minmax(110px, 1fr));
            gap: 10px;
            margin-bottom: 14px;
        }

        .aux-stats div {
            background: #fff;
            border: 1px solid #e3e8f0;
            border-radius: 8px;
            padding: 12px 14px;
            box-shadow: 0 4px 14px rgba(15, 23, 42, .05);
        }

        .aux-stats strong {
            display: block;
            font-size: 22px;
            line-height: 1;
        }

        .aux-stats span {
            color: #64748b;
            font-size: 12px;
        }

        .aux-team-band {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin: 12px 0;
            padding: 14px 16px;
            background: linear-gradient(90deg, #ecfdf5, #ffffff);
            border: 1px solid #bbf7d0;
            border-radius: 8px;
        }

        .aux-team-band span {
            display: block;
            color: #64748b;
            font-size: 12px;
        }

        .aux-team-band strong {
            font-size: 17px;
        }

        .aux-team-count {
            padding: 4px 10px;
            border-radius: 999px;
            background: #dcfce7;
            color: #15803d;
            font-size: 12px;
            font-weight: 700;
            white-space: nowrap;
        }

        .aux-talaea {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 12px;
        }

        .aux-taleia-card {
            background: #fff;
            border: 1px solid var(--aux-border);
            border-radius: 8px;
            box-shadow: 0 8px 24px rgba(37, 99, 235, .06);
            overflow: hidden;
        }

        .aux-taleia-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 14px;
            border-right: 4px solid var(--aux-accent);
            background: var(--aux-soft);
        }

        .aux-taleia-head h2 {
            margin: 4px 0 0;
            font-size: 16px;
            font-weight: 700;
            letter-spacing: 0;
        }

        .aux-taleia-head strong {
            min-width: 34px;
            height: 34px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: var(--aux-accent);
            color: #fff;
        }

        .aux-badge {
            color: var(--aux-accent);
            font-size: 11px;
            font-weight: 700;
        }

        .aux-people {
            display: flex;
            flex-direction: column;
            gap: 6px;
            padding: 12px;
        }

        .aux-person {
            display: flex;
            align-items: center;
            gap: 9px;
            padding: 8px;
            border: 1px solid #edf2f7;
            border-radius: 8px;
            background: #fbfdff;
        }

        .aux-avatar {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: var(--aux-avatar);
            color: var(--aux-accent);
            font-size: 12px;
            font-weight: 700;
            flex-shrink: 0;
            overflow: hidden;
        }

        .aux-avatar img {
            width: 100%;
            height: 100%;
            display: block;
            object-fit: cover;
        }

        .aux-person-info {
            flex: 1;
            min-width: 0;
        }

        .aux-person-info span {
            display: block;
            font-size: 13px;
            font-weight: 600;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .aux-person-info small {
            color: #64748b;
            direction: ltr;
            display: inline-block;
            font-size: 11px;
        }

        .aux-person em {
            color: #475569;
            background: #f1f5f9;
            border-radius: 999px;
            font-size: 11px;
            font-style: normal;
            padding: 2px 8px;
            white-space: nowrap;
        }

        .aux-empty,
        .aux-card-empty {
            background: #fff;
            border: 1px dashed #dbe3ef;
            border-radius: 8px;
            color: #64748b;
            font-size: 13px;
            padding: 20px;
            text-align: center;
        }

        .aux-card-empty {
            margin: 12px;
            padding: 14px;
        }

        @media (max-width: 720px) {
            .aux-root {
                padding: 16px 12px 36px;
            }

            .aux-header,
            .aux-filters {
                align-items: stretch;
                flex-direction: column;
            }

            .aux-select,
            .aux-select select {
                width: 100%;
            }

            .aux-stats {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
@endsection
