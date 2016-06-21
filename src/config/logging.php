<?php

namespace Corb\Logging;

/**
 * This class defines all the loggeable actions in your application. Here
 * you can add your own actions or change the predefined actions keys.
 */
abstract class Actions
{
    const CREATE = 'create';
    const UPDATE = 'update';
    const DELETE = 'delete';
}

return [
    /*
    |--------------------------------------------------------------------------
    | User model
    |--------------------------------------------------------------------------
    |
    | Indicates the user model for your application. It will be used as the
    | responsible.
    | This option is required.
    |
    */
    'user_model' => 'App\User',

    /*
    |--------------------------------------------------------------------------
    | Auth method
    |--------------------------------------------------------------------------
    |
    | This method is expected to return the current authenticated user in your
    | application. This is used to automatically set the responsible id when
    | an action is logged and a responsible id is not provided. If null, this
    | feature will be disabled.
    |
    */
    'auth_method' => '\Auth::user',

    /*
    |--------------------------------------------------------------------------
    | Contexts array
    |--------------------------------------------------------------------------
    |
    | Defines the available log contexts in your application. Each element will
    | define a one-to-one relationship in the Corb\Logging\Models\ActivityLog
    | model. The key indicates the name of the relationship, while the value is
    | the model to which the relationship points to. This way, you can access
    | the context in each of your logs using this property and also eager loading
    | your contexts when querying multiple logs.
    |
    */
    'contexts' => [
        'update_context' => 'Corb\Logging\Models\LogContextUpdate'
    ]
];
