<?php

namespace Corb\Logging;

use Corb\Logging\Models\ActivityLog;

/**
 * The Logger class provides a simple interface for logging.
 *
 * @author Jesús Barrera <jesus.barrera@corb.mx>
 * @since 1.0.0
 * @version 1.0.0
 */
class Logger
{
    /**
     * Creates a new log entry.
     *
     * @author Jesús Barrera <jesus.barrera@corb.mx>
     *
     * @param  string $action          Indicates the action being logged
     * @param  object $context         Represents the contextual data of the log
     * @param  number $responsible_id  Id of the user responsible for the action
     * @param  object $loggeable       The model being logged
     *
     * @return Corb\Logging\Models\ActivityLog   The created log
     */
    public static function create($action, $context = NULL, $responsible_id = NULL, $loggeable = NULL)
    {

        if (is_null($responsible_id)) {
            $responsible_id = static::getResponsibleId();
        }

        $log = new ActivityLog([
            'responsible_id' => $responsible_id,
            'action'         => $action
        ]);

        // Save log in loggeable model
        if (isset($loggeable)) {
            $loggeable->activityLogs()->save($log);
        } else {
            $log->save();
        }

        // Associate log with the context
        if (isset($context)) {
            $context->activityLog()->associate($log);
            $context->save();
        }

        return $log;
    }

    /**
     * Obtains responsible user id from current authenticated user.
     *
     * @author Jesús Barrera <jesus.barrera@corb.mx>
     *
     * @return mixed number|null
     */
    protected static function getResponsibleId()
    {
        $auth_method = config('logging.auth_method');

        if ($auth_method) {
            $user = $auth_method();

            if (isset($user)) {
                return $user->id;
            }
        }

        return NULL;
    }
}
