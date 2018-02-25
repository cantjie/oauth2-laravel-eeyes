<?php

return [
    'client' => [
        'id' => env('OAUTH_CLIENT_ID'),
        'secret' => env('OAUTH_CLIENT_SECRET'),
    ],
    'redirect_uri' => env('OAUTH_REDIRECT_URI')
];