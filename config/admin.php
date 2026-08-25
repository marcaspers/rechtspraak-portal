<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Initial admin account
    |--------------------------------------------------------------------------
    |
    | Used by Database\Seeders\AdminUserSeeder to create the first admin account (there is no
    | self-registration, see briefing §2.6). Change the password immediately after first login.
    |
    */
    'email' => env('ADMIN_EMAIL', 'admin@example.com'),
    'password' => env('ADMIN_PASSWORD', 'password'),

];
