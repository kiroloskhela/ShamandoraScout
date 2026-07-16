<?php

return [
    'blood' => [
        'route' => 'blood',
        'table' => 'BloodType',
        'primary_key' => 'BloodTypeID',
        'display_field' => 'BloodTypeName',
        'request_field' => 'blood_name',
        'views' => [
            'index' => 'blood.blood-index',
            'create' => 'blood.blood-create',
            'edit' => 'blood.blood-edit',
            'delete' => 'blood.blood-delete',
        ],
        'variables' => ['index' => 'blood', 'item' => 'blood'],
        'titles' => [
            'index' => 'فصائل الدم',
            'edit' => 'تعديل فصيلة دم',
            'delete' => 'حذف فصيلة دم',
        ],
        'messages' => [
            'store' => ' :تم ادخال بنجاح الفصيلة%s',
            'update' => ' :تم تعديل بنجاح الفصيلة%s',
            'destroy' => 'تم الغاء الفصيلة بنجاح',
        ],
    ],

    'faculty' => [
        'route' => 'faculty',
        'table' => 'Faculty',
        'primary_key' => 'FacultyID',
        'display_field' => 'FacultyName',
        'request_field' => 'faculty_name',
        'views' => [
            'index' => 'faculty.index',
            'create' => 'faculty.create',
            'edit' => 'faculty.edit',
            'delete' => 'faculty.delete',
        ],
        'variables' => ['index' => 'faculty', 'item' => 'faculty'],
    ],

    'university' => [
        'route' => 'university',
        'table' => 'University',
        'primary_key' => 'UniversityID',
        'display_field' => 'UniversityName',
        'request_field' => 'university_name',
        'views' => [
            'index' => 'university.index',
            'create' => 'university.create',
            'edit' => 'university.edit',
            'delete' => 'university.delete',
        ],
        'variables' => ['index' => 'university', 'item' => 'university'],
    ],

    'district' => [
        'route' => 'district',
        'table' => 'Districts',
        'primary_key' => 'DistrictID',
        'display_field' => 'DistrictName',
        'request_field' => 'district_name',
        'views' => [
            'index' => 'district.index',
            'create' => 'district.create',
            'edit' => 'district.edit',
            'delete' => 'district.delete',
        ],
        'variables' => ['index' => 'districts', 'item' => 'district'],
        'titles' => ['index' => 'فصائل الدم'],
    ],

    'manteqa' => [
        'route' => 'manteqa',
        'table' => 'Manteqa',
        'primary_key' => 'ManteqaID',
        'display_field' => 'ManteqaName',
        'request_field' => 'manteqa_name',
        'views' => [
            'index' => 'manteqa.index',
            'create' => 'manteqa.create',
            'edit' => 'manteqa.edit',
            'delete' => 'manteqa.delete',
        ],
        'variables' => ['index' => 'manateq', 'item' => 'manteqa'],
        'titles' => ['delete' => 'حذف فصيلة دم'],
    ],

    'group-type' => [
        'route' => 'group-type',
        'table' => 'GroupType',
        'primary_key' => 'GroupTypeID',
        'display_field' => 'GroupTypeName',
        'request_field' => 'group_type_name',
        'views' => [
            'index' => 'group-type.index',
            'create' => 'group-type.create',
            'edit' => 'group-type.edit',
            'delete' => 'group-type.delete',
        ],
        'variables' => ['index' => 'groupTypes', 'item' => 'groupType'],
    ],

    'event-type' => [
        'route' => 'event-type',
        'table' => 'EventType',
        'primary_key' => 'EventTypeID',
        'display_field' => 'EventTypeName',
        'request_field' => 'event_type_name',
        'views' => [
            'index' => 'event-type.index',
            'create' => 'event-type.create',
            'edit' => 'event-type.edit',
            'delete' => 'event-type.delete',
        ],
        'variables' => ['index' => 'eventTypes', 'item' => 'eventType'],
    ],

    'rotab' => [
        'route' => 'rotab',
        'table' => 'RotbaInformation',
        'primary_key' => 'RotbaID',
        'display_field' => 'RotbaName',
        'request_field' => 'rotba_name',
        'views' => [
            'index' => 'rotab.rotab-index',
            'create' => 'rotab.rotab-create',
            'edit' => 'rotab.rotab-edit',
            'delete' => 'rotab.rotab-delete',
        ],
        'variables' => ['index' => 'rotab', 'item' => 'rotab'],
        'titles' => [
            'index' => 'الرتب الكشفية',
            'edit' => 'تعديل رتبة كشفية',
            'delete' => 'حذف رتبة كشفية',
        ],
        'messages' => [
            'store' => ' :تم ادخال بنجاح الرتبة%s',
            'update' => ' :تم تعديل بنجاح الرتبة%s',
            'destroy' => 'تم الغاء الرتبة بنجاح',
        ],
    ],

    'marhala' => [
        'route' => 'marhala',
        'table' => 'Marhala',
        'primary_key' => 'MarhalaID',
        'display_field' => 'MarhalaName',
        'request_field' => 'marhala_name',
        'views' => [
            'index' => 'marhala.marhala-index',
            'create' => 'marhala.marhala-create',
            'edit' => 'marhala.marhala-edit',
            'delete' => 'marhala.marhala-delete',
        ],
        'variables' => ['index' => 'marhala', 'item' => 'marhala'],
        'titles' => [
            'index' => 'الرتب الكشفية',
            'edit' => 'تعديل مرحلة دراسية',
            'delete' => 'حذف مرحلة دراسية',
        ],
        'messages' => [
            'store' => ' :تم ادخال بنجاح المرحلة%s',
            'update' => ' :تم تعديل بنجاح المرحلة%s',
            'destroy' => 'تم الغاء المرحلة بنجاح',
        ],
    ],

    'sana-marhala' => [
        'route' => 'sana-marhala',
        'table' => 'SanaMarhala',
        'primary_key' => 'SanaMarhalaID',
        'display_field' => 'SanaMarhalaName',
        'request_field' => 'sana_marhala_name',
        'insert_defaults' => [
            'SanaID' => 0,
            'MarhalaID' => 6,
        ],
        'views' => [
            'index' => 'sana-marhala.index',
            'create' => 'sana-marhala.create',
            'edit' => 'sana-marhala.edit',
            'delete' => 'sana-marhala.delete',
        ],
        'variables' => ['index' => 'sana', 'item' => 'sana'],
    ],

    'qetaa' => [
        'route' => 'qetaa',
        'table' => 'Qetaa',
        'primary_key' => 'QetaaID',
        'display_field' => 'QetaaName',
        'request_field' => 'qetaa_name',
        'views' => [
            'index' => 'qetaa.index',
            'create' => 'qetaa.create',
            'edit' => 'qetaa.edit',
            'delete' => 'qetaa.delete',
        ],
        'variables' => ['index' => 'qetaat', 'item' => 'qetaa'],
        'titles' => [
            'edit' => 'تعديل فصيلة دم',
            'delete' => 'حذف فصيلة دم',
        ],
        'messages' => [
            'store' => ' :تم ادخال بنجاح الفصيلة%s',
            'update' => ' :تم تعديل بنجاح الفصيلة%s',
            'destroy' => 'تم الغاء الفصيلة بنجاح',
        ],
    ],

    'role' => [
        'route' => 'role',
        'table' => 'Roles',
        'primary_key' => 'RoleID',
        'display_field' => 'RoleName',
        'request_field' => 'role_name',
        // RoleDescription is NOT NULL in production schema.
        'insert_defaults' => [
            'RoleDescription' => '',
        ],
        'views' => [
            'index' => 'role.index',
            'create' => 'role.create',
            'edit' => 'role.edit',
            'delete' => 'role.delete',
        ],
        'variables' => ['index' => 'roles', 'item' => 'role'],
    ],
];

