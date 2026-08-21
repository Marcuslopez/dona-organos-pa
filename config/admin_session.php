<?php

return [
    'idle_timeout' => (int) env('ADMIN_IDLE_TIMEOUT', 900),
    'idle_warning' => (int) env('ADMIN_IDLE_WARNING', 60),
    'max_lifetime' => (int) env('ADMIN_SESSION_MAX_LIFETIME', 28800),
];
