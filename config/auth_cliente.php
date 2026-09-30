<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Client Authentication Method
    |--------------------------------------------------------------------------
    |
    | Supported methods for registered clients:
    | - 'password': (Default, most secure) Requires DNI/username and password.
    | - 'dni'     : Passwordless, direct access with DNI only (restricted to 'Cliente' role).
    | - 'otp'     : One-Time Password sent via WhatsApp / mobile.
    |
    | Can be overridden at runtime via the `businesses.auth_cliente_metodo` database column.
    |
    */
    'metodo' => env('AUTH_CLIENTE_METODO', 'password'),
];
