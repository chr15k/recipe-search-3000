<?php

return [

    'cache' => [
        'enabled' => env('SEARCH_CACHE_ENABLED', true),
        'duration' => env('SEARCH_CACHE_DURATION_MINUTES', 15), // in minutes
    ],
];
