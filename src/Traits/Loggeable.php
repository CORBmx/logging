<?php

namespace Corb\Logging\Traits;

use Corb\Logging\Models\UpdateLogContext;
use Corb\Logging\Logger;
use Corb\Logging\Actions;

/**
 * This trait allows the model that uses it to register new entries in the activity
 * log table.
 *
 * @author Jesús Barrera <jesus.barrera@corb.mx>
 */
trait Loggeable
{
    /**
     * Creates a new log entry for this model.
     *
     * @param  string $action           Indicates the action being logged
     * @param  object $context          Represents the contextual data of the log
     * @param  number $responsible_id   Id of the user responsible for the action
     *
     * @return Corb\Logging\Models\ActivityLog   The created log
     */
    public function createLog($action, $context = NULL, $responsible_id = NULL)
    {
        return Logger::create($action, $context, $responsible_id, $this);
    }

    /**
     * Returns the activity logs for this model.
     *
     * @return MorphMany
     */
    public function activityLogs()
    {
        return $this->morphMany('Corb\Logging\Models\ActivityLog', 'loggeable');
    }
}
