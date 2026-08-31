<?php

return [
    'admin' => [
        'code_resend_after' => (int) env('ADMIN_LOGIN_CODE_RESEND_AFTER', 30),
        'code_max_attempts' => (int) env('ADMIN_LOGIN_CODE_MAX_ATTEMPTS', 3),
        'password_max_attempts' => (int) env('ADMIN_PASSWORD_MAX_ATTEMPTS', 3),
        'lockout_seconds' => (int) env('ADMIN_LOGIN_LOCKOUT_SECONDS', 180),
    ],
    'donor' => [
        'code_resend_after' => (int) env('DONOR_ACCESS_CODE_RESEND_AFTER', 30),
        'code_max_attempts' => (int) env('DONOR_ACCESS_CODE_MAX_ATTEMPTS', 3),
        'lockout_seconds' => (int) env('DONOR_ACCESS_LOCKOUT_SECONDS', 30),
    ],
];
