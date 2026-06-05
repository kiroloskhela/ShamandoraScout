@php
    $groupTypeNames = [2 => 'طليعة', 3 => 'فريق'];
    $badgeClass = $group->GroupTypeID == 2 ? 'badge-2' : 'badge-3';
@endphp

<div class="group-block">
    <div class="group-header" onclick="toggleGroup(this)">
        <span class="group-chevron">›</span>
        <span class="group-title">{{ $group->GroupName }}</span>
        <span class="badge {{ $badgeClass }}">{{ $groupTypeNames[$group->GroupTypeID] ?? 'مجموعة' }}</span>
        @if ($isServed)
            <div class="group-actions" onclick="event.stopPropagation()">
                @if ($group->GroupTypeID == 3)
                    <button class="add-btn" onclick="event.stopPropagation(); openGroupModal({{ $qetaaId }}, 2, {{ $group->GroupID }})" title="إضافة طليعة">+ طليعة</button>
                    <button class="add-btn add-btn--alt" onclick="event.stopPropagation(); openGroupModal({{ $qetaaId }}, 3, 0)" title="إضافة فريق">+ فريق</button>
                @endif
                @if ($group->GroupTypeID == 2)
                    <button class="add-btn" onclick="event.stopPropagation(); openPersonModal({{ $group->GroupID }})" title="إضافة شخص">+ شخص</button>
                @endif
                <button class="delete-btn" onclick="event.stopPropagation(); deleteGroup({{ $group->GroupID }})">حذف</button>
            </div>
        @endif
    </div>

    <div class="group-body">
        @if ($group->people->isEmpty())
            <p class="empty-msg">لا يوجد أعضاء في هذه {{ $groupTypeNames[$group->GroupTypeID] ?? 'المجموعة' }}</p>
        @else
            <div class="people-section">
                <div class="people-section-title">الأعضاء</div>
                @foreach ($group->people as $person)
                    <div class="person-row">
                        <div class="person-name">{{ trim($person->FirstName . ' ' . $person->SecondName) }}</div>
                        @if (!empty($person->RotbaName))
                            <span class="person-rotba">{{ $person->RotbaName }}</span>
                        @endif
                        @if ($isServed)
                            <button class="delete-btn" onclick="removePerson({{ $person->PersonID }}, {{ $group->GroupID }})">إزالة</button>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif

        @if ($group->children->isEmpty())
            @if ($group->GroupTypeID == 3)
                <p class="empty-msg">لا توجد طليعات ضمن هذا الفريق</p>
            @endif
        @else
            @foreach ($group->children as $child)
                @php
                    $childBadgeClass = $child->GroupTypeID == 2 ? 'badge-2' : 'badge-3';
                @endphp
                <div class="subgroup-block">
                    <div class="subgroup-header" onclick="toggleGroup(this)">
                        <span class="subgroup-chevron">›</span>
                        <span class="subgroup-title">{{ $child->GroupName }}</span>
                        <span class="badge {{ $childBadgeClass }}">{{ $groupTypeNames[$child->GroupTypeID] ?? 'مجموعة' }}</span>
                        @if ($isServed)
                            <div class="group-actions" onclick="event.stopPropagation()">
                                @if ($child->GroupTypeID == 2)
                                    <button class="add-btn" onclick="event.stopPropagation(); openPersonModal({{ $child->GroupID }})" title="إضافة شخص">+ شخص</button>
                                @endif
                                <button class="delete-btn" onclick="event.stopPropagation(); deleteGroup({{ $child->GroupID }})">حذف</button>
                            </div>
                        @endif
                    </div>
                    <div class="subgroup-body">
                        @if ($child->people->isEmpty())
                            <p class="empty-msg">لا يوجد أعضاء في هذه {{ $groupTypeNames[$child->GroupTypeID] ?? 'المجموعة' }}</p>
                        @else
                            <div class="people-section">
                                <div class="people-section-title">الأعضاء</div>
                                @foreach ($child->people as $person)
                                    <div class="person-row">
                                        <div class="person-name">{{ trim($person->FirstName . ' ' . $person->SecondName) }}</div>
                                        @if (!empty($person->RotbaName))
                                            <span class="person-rotba">{{ $person->RotbaName }}</span>
                                        @endif
                                        @if ($isServed)
                                            <button class="delete-btn" onclick="removePerson({{ $person->PersonID }}, {{ $child->GroupID }})">إزالة</button>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        @endif
    </div>
</div>
