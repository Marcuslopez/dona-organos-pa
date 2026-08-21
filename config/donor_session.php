<?php

return [
    'idle_timeout' => (int) env('DONOR_IDLE_TIMEOUT', 900),
    'idle_warning' => (int) env('DONOR_IDLE_WARNING', 60),
    'max_lifetime' => (int) env('DONOR_SESSION_MAX_LIFETIME', 0),
];
