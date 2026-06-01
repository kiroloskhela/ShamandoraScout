<?php
 
return [
    'android' => [
        'latest_version' => '1.0.5',
        'min_version'    => '1.0.5',
        'force_update'   => false,
        'url'            => 'https://play.google.com/store/apps/details?id=com.example.app',
    ],
    'ios' => [
        'latest_version' => '1.0.5',
        'min_version'    => '1.0.5',
        'force_update'   => false,
        'url'            => 'https://apps.apple.com/app/id123456789',
    ],
    'maintenance' => [
        'enabled' => false,
        'message' => 'Server under maintenance',
    ],
    'update_ui' => [
        'title'   => 'Update Required',
        'message' => 'Please update the app',
        'button'  => 'Update',
    ],
];
 