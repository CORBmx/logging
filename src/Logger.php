<?php

namespace Corb\Logging;

use Auth;
use Corb\Logging\Models\ActivityLog;

class Logger
{
    /**
     * Defined actions
     */
    const UPDATE = 'update';
    const CREATE = 'create';
    const DELETE = 'delete';

    /**
     * Creates a new log entry.
     *
     * @author Jesús Barrera <jesus.barrera@corb.mx>
     * @since 0.1.0
     * @version 0.1.0
     *
     * @param  string $action           Indicates the action being logged
     * @param  object $context          Represents the contextual data of the log
     * @param  number $responsible_id   Id of the user responsible for the action
     * @param  object $loggeable        The model being logged
     *
     * @return App\Models\ActivityLog   The created log
     */
    public static function create($action, $context = NULL, $responsible_id = NULL, $loggeable = NULL)
    {
        // get responsible user
        if (!isset($responsible_id)) {
            $user = Auth::User();

            if (isset($user)) {
                $responsible_id = $user->id;
            }
        }

        // create log instance
        $log = new ActivityLog([
            'responsible_id' => $responsible_id,
            'action'         => $action
        ]);

        if (isset($loggeable)) {
            // save log in the loggeable model
            $loggeable->activityLogs()->save($log);
        } else {
            $log->save();
        }

        if (isset($context)) {
            // associate and save log context
            $context->activityLog()->associate($log);
            $context->save();
        }

        return $log;
    }
}
