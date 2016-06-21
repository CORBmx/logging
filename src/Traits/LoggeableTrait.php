<?php

namespace Corb\Logging\Traits;

use Corb\Logging\Models\LogContextUpdate;
use Corb\Logging\Logger;
use Corb\Logging\Actions;

/**
 * This trait allows the model that uses it, to register new entries in the activity
 * log table. Also, specific model events are set, so the CREATE, UPDATE, and DELETE
 * actions are logged automatically.
 *
 * @author Jesús Barrera <jesus.barrera@corb.mx>
 */
trait LoggeableTrait
{
    /**
     * Registers listeners for the created, updating and deleted events.
     * 
     * @author Jesús Barrera <jesus.barrera@corb.mx>
     * @return None
     */
    public static function bootLoggeableTrait()
    {
        static::created(function ($model) {
            $model->createLog(Actions::CREATE);
        });

        static::updating(function ($model) {
            $after  = $model->getDirty();
            $before = array_intersect_key($model->getOriginal(), $after);

            $context = new LogContextUpdate();

            $context->after  = $after;
            $context->before = $before;

            $log = $model->createLog(Actions::UPDATE, $context);
        });

        static::deleted(function ($model) {
            $model->createLog(Actions::DELETE);
        });
    }

    /**
     * Creates a new log entry for this model.
     *
     * @author Jesús Barrera <jesus.barrera@corb.mx>
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
     * @author Jesús Barrera <jesus.barrera@corb.mx>
     *
     * @return MorphMany
     */
    public function activityLogs()
    {
        return $this->morphMany('Corb\Logging\Models\ActivityLog', 'loggeable');
    }
}
