<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Client Login Mode (Feature Flag)
    |--------------------------------------------------------------------------
    |
    | Supported modes:
    | - 'password': (default) Requires username/document and password.
    | - 'id_only': Allows clients to authenticate using only their registered ID / document.
    |
    | Meeting decision: Registered user access method (password vs. ID only)
    | is placed behind this configurable feature flag.
    |
    */
    'client_login_mode' => env('CLIENT_LOGIN_MODE', 'password'),
];
