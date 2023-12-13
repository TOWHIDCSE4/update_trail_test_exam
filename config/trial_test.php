<?php

return [
    'main_type' => [
        'sort' => 1,
        'q_and_a' => 2,
        'matching' => 3,
        'fill_input' => 4,
        'drop_down' => 5
    ],
    'category' => [
        'vocabulary' => 1,
        'reading' => 2,
        'writing' => 3,
        'grammar' => 4,
        // 'listening' => 5,
        'ielts_writing' => 6
    ],
    'role_code' => [
        'super_admin' => 0,
        'student' => 1,
        'teacher' => 2,
        'admin' => 3,
        'config_system' => 4,
        'report' => 5,
    ],
    'test_type' => [
        'normal' => 1,
        'staff_pre_test' => 2
    ],
    'enum_role' => [
        'manager' => 'manager',
        'leader' => 'leader',
        'staff' => 'staff'
    ],
    'library_test' => [
        'publish_status' => [
            'draft' => 1,
            'published' => 2
        ]
    ],
    'topic' => [
        'test_type' => [
            'common' => 'COMMON',
            'ielts_grammar' => 'IELTS_GRAMMAR',
            'ielts_writing' => 'IELTS_WRITING',
            'ielts_listening' => 'IELTS_LISTENING',
            'ielts_reading' => 'IELTS_READING'
        ]
    ],
    'result_type' => [
        'homework' => 1,
        'homework_ielts' => 2
    ],
    'topic_test_type' => [
        'en_common' => 'EN_COMMON',
        'ielts_grammar' => 'IELTS_GRAMMAR',
        'ielts_reading' => 'IELTS_READING',
        'ielts_listening' => 'IELTS_LISTENING'
    ],
];
