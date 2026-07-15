{{-- resources/views/tree/_group.blade.php --}}
@php
    $typeLabel = [2 => 'فريق', 3 => 'طليعة'];
    $badgeClass = [2 => 'qt-pill--green', 3 => 'qt-pill--blue'];
    $initials = fn($name) => mb_substr($name, 0, 1, 'UTF-8');
    $fullName = fn($person) => trim(collect([$person->FirstName ?? null, $person->SecondName ?? null, $person->ThirdName ?? null, $person->FourthName ?? null])->filter()->implode(' '));
    $isFareeq = (int) $group->GroupTypeID === 2;
    $isTaleia = (int) $group->GroupTypeID === 3;
@endphp

<div class="qt-group {{ $isFareeq ? 'qt-group--fareeq' : 'qt-group--taleia' }}" data-group-id="{{ $group->GroupID }}">

    {{-- Group header --}}
    <div class="qt-group__head" onclick="qtToggle(this)">
        <span class="qt-group-chevron">
            <svg viewBox="0 0 8 13" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M2 2l4 4.5-4 4.5" />
            </svg>
        </span>

        <span class="qt-group__name">{{ $group->GroupName }}</span>
        <span class="qt-pill {{ $badgeClass[$group->GroupTypeID] ?? '' }}">
            {{ $typeLabel[$group->GroupTypeID] ?? '' }}
        </span>

        <div class="qt-group__actions" onclick="event.stopPropagation()">
            @if ($isServed)
                @if ($isFareeq)
                    <button class="qt-icon-btn" title="إضافة طليعة داخل الفريق"
                        onclick="openGroupModal({{ $qetaaId }}, 3, {{ $group->GroupID }})">
                        <svg viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M6 2v8M2 6h8" />
                        </svg>
                    </button>
                @endif
                @if ($isTaleia)
                    <button class="qt-icon-btn" title="إضافة شخص" onclick="openPersonModal({{ $group->GroupID }})">
                        <svg viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="1.8">
                            <circle cx="6" cy="4" r="3" />
                            <path d="M1 13c0-2.8 2.2-5 5-5s5 2.2 5 5" />
                            <path d="M11 6v4M13 8h-4" />
                        </svg>
                    </button>
                @endif
                <button class="qt-icon-btn qt-icon-btn--danger" title="حذف المجموعة"
                    onclick="deleteGroup({{ $group->GroupID }})">
                    <svg viewBox="0 0 12 14" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M1 3h10M4 3V2h4v1M2 3l1 9h6l1-9" />
                    </svg>
                </button>
            @endif
        </div>
    </div>

    {{-- Group body --}}
    <div class="qt-group__body">

        {{-- Sub-groups (children) --}}
        @if ($group->children->isNotEmpty())
            <div class="qt-subgroups">
                @foreach ($group->children as $child)
                    <div class="qt-subgroup">
                        <div class="qt-subgroup__head" onclick="qtToggle(this)">
                            <span class="qt-group-chevron">
                                <svg viewBox="0 0 8 13" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M2 2l4 4.5-4 4.5" />
                                </svg>
                            </span>
                            <span class="qt-subgroup__name">{{ $child->GroupName }}</span>
                            <span class="qt-pill {{ $badgeClass[$child->GroupTypeID] ?? '' }}">
                                {{ $typeLabel[$child->GroupTypeID] ?? '' }}
                            </span>

                            @if ($isServed && (int) $child->GroupTypeID === 3)
                                <div class="qt-group__actions" onclick="event.stopPropagation()">
                                    <button class="qt-icon-btn" title="إضافة شخص"
                                        onclick="openPersonModal({{ $child->GroupID }})">
                                        <svg viewBox="0 0 14 14" fill="none" stroke="currentColor"
                                            stroke-width="1.8">
                                            <circle cx="6" cy="4" r="3" />
                                            <path d="M1 13c0-2.8 2.2-5 5-5s5 2.2 5 5" />
                                            <path d="M11 6v4M13 8h-4" />
                                        </svg>
                                    </button>
                                    <button class="qt-icon-btn qt-icon-btn--danger" title="{{ __('Delete') }}"
                                        onclick="deleteGroup({{ $child->GroupID }})">
                                        <svg viewBox="0 0 12 14" fill="none" stroke="currentColor"
                                            stroke-width="1.8">
                                            <path d="M1 3h10M4 3V2h4v1M2 3l1 9h6l1-9" />
                                        </svg>
                                    </button>
                                </div>
                            @endif
                        </div>

                        <div class="qt-subgroup__body">
                            @if ($child->people->isEmpty())
                                <p class="qt-empty">لا أعضاء في هذه الطليعة</p>
                            @else
                                <div class="qt-people">
                                    @foreach ($child->people as $person)
                                        @php
                                            $personFullName = $fullName($person);
                                            $imagePath = $person->PersonSystemImagePath ?? null;
                                            $imageSrc = $imagePath ? asset('storage/' . $imagePath) : null;
                                        @endphp
                                        <div class="qt-person">
                                            <div class="qt-person__avatar">
                                                @if ($imageSrc)
                                                    <img src="{{ $imageSrc }}" alt="{{ $personFullName }}">
                                                @else
                                                    {{ $initials($person->FirstName ?? '؟') }}
                                                @endif
                                            </div>
                                            <div class="qt-person__info">
                                                <div class="qt-person__name">
                                                    {{ $personFullName }}
                                                </div>
                                                @if ($person->ShamandoraCode)
                                                    <span class="qt-person__code">{{ $person->ShamandoraCode }}</span>
                                                @endif
                                            </div>
                                            @if ($person->RotbaName)
                                                <span class="qt-person__rotba">{{ $person->RotbaName }}</span>
                                            @endif
                                            @if ($isServed)
                                                <button class="qt-icon-btn" title="تعديل الرتبة"
                                                    onclick="openRotbaModal({{ $person->PersonID }}, {{ $child->GroupID }}, {{ $person->RotbaID ? (int) $person->RotbaID : 'null' }})">
                                                    <svg viewBox="0 0 14 14" fill="none" stroke="currentColor"
                                                        stroke-width="1.8">
                                                        <path d="M7 2h5M7 7h5M7 12h5" />
                                                        <path d="M2 3l1 1 2-2M2 8l1 1 2-2M2 13l1 1 2-2" />
                                                    </svg>
                                                </button>
                                                <button class="qt-icon-btn qt-icon-btn--danger" title="{{ __('Remove') }}"
                                                    onclick="removePerson({{ $person->PersonID }}, {{ $child->GroupID }})">
                                                    <svg viewBox="0 0 12 14" fill="none" stroke="currentColor"
                                                        stroke-width="1.8">
                                                        <path d="M1 3h10M4 3V2h4v1M2 3l1 9h6l1-9" />
                                                    </svg>
                                                </button>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- Direct people in talaea only --}}
        @if ($isTaleia && $group->people->isNotEmpty())
            <div class="qt-people">
                @foreach ($group->people as $person)
                    @php
                        $personFullName = $fullName($person);
                        $imagePath = $person->PersonSystemImagePath ?? null;
                        $imageSrc = $imagePath ? asset('storage/' . $imagePath) : null;
                    @endphp
                    <div class="qt-person">
                        <div class="qt-person__avatar">
                            @if ($imageSrc)
                                <img src="{{ $imageSrc }}" alt="{{ $personFullName }}">
                            @else
                                {{ $initials($person->FirstName ?? '؟') }}
                            @endif
                        </div>
                        <div class="qt-person__info">
                            <div class="qt-person__name">
                                {{ $personFullName }}
                            </div>
                            @if ($person->ShamandoraCode)
                                <span class="qt-person__code">{{ $person->ShamandoraCode }}</span>
                            @endif
                        </div>
                        @if ($person->RotbaName)
                            <span class="qt-person__rotba">{{ $person->RotbaName }}</span>
                        @endif
                        @if ($isServed)
                            <button class="qt-icon-btn" title="تعديل الرتبة"
                                onclick="openRotbaModal({{ $person->PersonID }}, {{ $group->GroupID }}, {{ $person->RotbaID ? (int) $person->RotbaID : 'null' }})">
                                <svg viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path d="M7 2h5M7 7h5M7 12h5" />
                                    <path d="M2 3l1 1 2-2M2 8l1 1 2-2M2 13l1 1 2-2" />
                                </svg>
                            </button>
                            <button class="qt-icon-btn qt-icon-btn--danger" title="{{ __('Remove') }}"
                                onclick="removePerson({{ $person->PersonID }}, {{ $group->GroupID }})">
                                <svg viewBox="0 0 12 14" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path d="M1 3h10M4 3V2h4v1M2 3l1 9h6l1-9" />
                                </svg>
                            </button>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif

        @if ($group->children->isEmpty() && (!$isTaleia || $group->people->isEmpty()))
            <p class="qt-empty">{{ $isFareeq ? 'لا توجد طلايع داخل هذا الفريق' : 'لا أعضاء في هذه الطليعة' }}</p>
        @endif
    </div>
</div>
