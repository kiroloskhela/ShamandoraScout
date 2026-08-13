<?php

/**
 * Permission catalog (code-owned keys) and seed map (server-truth RoleName lists).
 *
 * SuperAdmin is never seeded — PermissionService::isSuperAdmin() bypasses the matrix.
 * Key grammar: {platform}.{domain}.{action}  platform = web|mobile|api
 */

$staff = [
    'Secretary', 'Media', 'Inventory', 'Finance', 'AdminQetaa',
    'AdminSecretary', 'AdminInventory', 'AdminFinance', 'AdminFirstAid', 'Khadem',
];

$keys = [
    'web.system.manage' => ['label' => 'System constants, lookups, WhatsApp, camp programs', 'platform' => 'web', 'danger' => true],
    'web.people.manage' => ['label' => 'Person CRUD, special cases, blacklist, waiting list', 'platform' => 'web'],
    'web.people.directory' => ['label' => 'Person directory and Excel export', 'platform' => 'web'],
    'web.enrolments.manage' => ['label' => 'Review and approve enrolments', 'platform' => 'web'],
    'web.enrolments.unscoped' => ['label' => 'Enrolments without sector scope', 'platform' => 'web', 'danger' => true],
    'web.enrolments.migrate' => ['label' => 'Migrate enrolments to main system', 'platform' => 'web', 'danger' => true],
    'web.registrations.manage' => ['label' => 'Guests, family, person tree', 'platform' => 'web'],
    'web.finance.manage' => ['label' => 'Event booking finance', 'platform' => 'web'],
    'web.finance.delete_booking' => ['label' => 'Delete event bookings', 'platform' => 'web', 'danger' => true],
    'web.secretary.manage' => ['label' => 'Minutes, places, events, booking review', 'platform' => 'web'],
    'web.inventory.manage' => ['label' => 'Inventory stock and custody queue', 'platform' => 'web'],
    'web.inventory.review' => ['label' => 'Approve or reject custody requests', 'platform' => 'web'],
    'web.medicine.manage' => ['label' => 'Medicine inventory', 'platform' => 'web'],
    'web.attendance.live' => ['label' => 'Live attendance board', 'platform' => 'web'],
    'web.games.manage' => ['label' => 'Create and edit games', 'platform' => 'web'],
    'web.people.view_any' => ['label' => 'View any person profile (no Qetaa scope)', 'platform' => 'web', 'danger' => true],
    'web.people.update_any' => ['label' => 'Update any person (no Qetaa scope)', 'platform' => 'web', 'danger' => true],
    'web.people.delete_any' => ['label' => 'Delete any person', 'platform' => 'web', 'danger' => true],

    'mobile.profile.own' => ['label' => 'Own profile', 'platform' => 'mobile'],
    'mobile.attendance.own' => ['label' => 'Own attendance history', 'platform' => 'mobile'],
    'mobile.attendance.take' => ['label' => 'Take attendance for served people', 'platform' => 'mobile'],
    'mobile.members.list' => ['label' => 'Members roster', 'platform' => 'mobile'],
    'mobile.special_cases.manage' => ['label' => 'Person special cases', 'platform' => 'mobile'],
    'mobile.inventory.catalog' => ['label' => 'Inventory stock catalog', 'platform' => 'mobile'],
    'mobile.inventory.requests' => ['label' => 'Own inventory requests', 'platform' => 'mobile'],
    'mobile.lectures.own' => ['label' => 'Own lectures (content later)', 'platform' => 'mobile'],
    'mobile.media.own' => ['label' => 'Own media (content later)', 'platform' => 'mobile'],
    'mobile.games.view' => ['label' => 'View games', 'platform' => 'mobile'],
    'mobile.games.manage' => ['label' => 'Create and edit games', 'platform' => 'mobile'],

    'api.mobile.staff' => ['label' => 'Core mobile APIs (list, attendance, media, own custody)', 'platform' => 'api'],
    'api.special_cases.manage' => ['label' => 'Special-cases API', 'platform' => 'api'],
    'api.games.mutate' => ['label' => 'Create/update/delete games via API', 'platform' => 'api', 'danger' => true],
];

$mobileStaff = [
    'api.mobile.staff',
    'mobile.profile.own',
    'mobile.attendance.take',
    'mobile.members.list',
    'mobile.inventory.requests',
    'mobile.games.view',
    'mobile.lectures.own',
    'mobile.media.own',
];

return [
    'enforce' => (bool) env('PERMISSIONS_ENFORCE', false),
    'non_grantable' => [
        'web.matrix.manage',
        'web.admin.passwords',
        'web.roles.assign_superadmin',
        'web.audit.purge',
        'web.security.config',
    ],
    'keys' => $keys,
    'seed' => [
        'AdminQetaa' => array_values(array_unique(array_merge($mobileStaff, [
            'web.people.manage', 'web.people.directory', 'web.enrolments.manage',
            'api.special_cases.manage', 'mobile.special_cases.manage',
        ]))),
        'AdminSecretary' => array_values(array_unique(array_merge($mobileStaff, [
            'web.people.directory', 'web.enrolments.manage', 'web.enrolments.unscoped',
            'web.registrations.manage', 'web.secretary.manage', 'web.attendance.live',
        ]))),
        'Secretary' => array_values(array_unique(array_merge($mobileStaff, [
            'web.people.directory', 'web.enrolments.manage', 'web.enrolments.unscoped',
            'web.registrations.manage', 'web.secretary.manage', 'web.attendance.live',
        ]))),
        'AdminFinance' => array_values(array_unique(array_merge($mobileStaff, [
            'web.people.directory', 'web.enrolments.manage', 'web.enrolments.unscoped',
            'web.registrations.manage', 'web.finance.manage', 'web.attendance.live',
        ]))),
        'Finance' => array_values(array_unique(array_merge($mobileStaff, [
            'web.registrations.manage', 'web.finance.manage', 'web.attendance.live',
        ]))),
        'AdminInventory' => array_values(array_unique(array_merge($mobileStaff, [
            'web.inventory.manage', 'web.inventory.review', 'mobile.inventory.catalog',
        ]))),
        'Inventory' => array_values(array_unique(array_merge($mobileStaff, [
            'web.inventory.manage', 'mobile.inventory.catalog',
        ]))),
        'AdminFirstAid' => array_values(array_unique(array_merge($mobileStaff, [
            'web.medicine.manage',
        ]))),
        'Media' => $mobileStaff,
        'Khadem' => array_values(array_unique(array_merge($mobileStaff, [
            'web.people.directory',
        ]))),
    ],
    'staff_roles' => $staff,
];
