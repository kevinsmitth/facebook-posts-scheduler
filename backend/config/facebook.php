<?php

return [
    'app_id' => env('FACEBOOK_APP_ID'),
    'app_secret' => env('FACEBOOK_APP_SECRET'),
    'redirect_uri' => env('FACEBOOK_REDIRECT_URI'),
    'graph_version' => env('FACEBOOK_GRAPH_VERSION', 'v23.0'),
    'beta_mode' => env('FACEBOOK_ENABLE_BETA', false),
    'page_id' => env('FACEBOOK_PAGE_ID'),
    'page_access_token' => env('FACEBOOK_PAGE_ACCESS_TOKEN'),
];
