<?php

return [
    /*
    |--------------------------------------------------------------------------
    | User model
    |--------------------------------------------------------------------------
    |
    | Indicates the user model for your application. It will be used as the responsible.
    | This option is required.
    |
    */
    'user_model' => 'App\User',

    /*
    |--------------------------------------------------------------------------
    | Auth metod
    |--------------------------------------------------------------------------
    |
    | This method is expected to return the current authenticated user on your
    | application. It's used to automatically set the responsible id when a new
    | log is created and a responsible id is not provided. If null, this feature
    | will be disabled.
    |
    */
    'auth_method' => '\Auth::user'
];
