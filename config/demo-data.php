<?php

return [
    'enabled' => (bool) env('DEMO_DATA_ENABLED', false),
    'records' => (int) env('DEMO_DATA_RECORDS', 300),
    'maximum_records' => (int) env('DEMO_DATA_MAX_RECORDS', 1000),
    'months' => (int) env('DEMO_DATA_MONTHS', 24),
    'seed' => (int) env('DEMO_DATA_SEED', 20260813),
];
