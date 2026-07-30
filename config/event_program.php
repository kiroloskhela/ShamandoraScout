<?php

return [
    'camp_event_types' => [
        'معسكر مجمع',
        'معسكر',
        'يوم مجمع',
    ],

    'gemini' => [
        'api_key' => env('GEMINI_API_KEY'),
        'model' => env('EVENT_PROGRAM_AI_MODEL', 'gemini-2.5-flash'),
        'endpoint' => env(
            'GEMINI_API_ENDPOINT',
            'https://generativelanguage.googleapis.com/v1beta/models'
        ),
        'timeout' => (int) env('GEMINI_REQUEST_TIMEOUT', 45),
    ],

    'default_intro' => "أهلاً يا {title} {name}\n\nده برنامجك لليوم {day} في {event_name}\n",
    'default_outro' => "\nشكراً على تعبك، وصلّي من أجل الخدمة ❤️",

    'guide_xlsx' => 'templates/event_program_guide.xlsx',
];
