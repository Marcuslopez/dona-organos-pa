<?php

return [
    'provider' => env('IDENTITY_PROVIDER', 'simulated'),
    'allow_test_identities' => env('ALLOW_TEST_IDENTITIES', false),
    'verification_ttl_minutes' => (int) env('IDENTITY_VERIFICATION_TTL_MINUTES', 10),
    'max_attempts' => (int) env('IDENTITY_MAX_ATTEMPTS', 3),
    'lockout_seconds' => (int) env('IDENTITY_LOCKOUT_SECONDS', 30),
];
