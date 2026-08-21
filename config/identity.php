<?php

return [
    'provider' => env('IDENTITY_PROVIDER', 'simulated'),
    'allow_test_identities' => env('ALLOW_TEST_IDENTITIES', false),
    'max_attempts' => (int) env('IDENTITY_MAX_ATTEMPTS', 3),
    'lockout_seconds' => (int) env('IDENTITY_LOCKOUT_SECONDS', 30),
];
