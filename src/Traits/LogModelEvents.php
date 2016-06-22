<?php

namespace Corb\Logging\Traits;

use Corb\Logging\Models\UpdateLogContext;
use Corb\Logging\Actions;

/**
 * Automatically log the CREATE, UPDATE, and DELETE actions on the model.
 *
 * @author Jesús Barrera <jesus.barrera@corb.mx>
 */
trait LogModelEvents
{
    /**
     * Registers listeners for the created, updated and deleted events.
     */
    public static function bootLogModelEvents()
    {
        // Create new object instance so we can access the log_events property.
        $obj = new static();

        // If the log_events property is set in the model, then use it. If not,
        // log all events.
        if (isset($obj->log_events)) {
            $events = $obj->log_events;
        } else {
            $events = ['created', 'updated', 'deleted'];
        }

        // Register necessary event handlers to log the specified events

        if (in_array('created', $events)) {
            static::created(function ($model) {
                $model->afterCreate();
            });
        }

        if (in_array('updated', $events)) {
            static::updating(function ($model)
            {
                $model->beforeUpdate();
            });
        }

        if (in_array('deleted', $events)) {
            static::deleted(function ($model) {
                $model->afterDelete();
            });
        }
    }

    /**
     * Called after a the model creation. Creates a new log for this action.
     */
    protected function afterCreate()
    {
        $this->createLog(Actions::CREATE);
    }

    /**
     * Called after the model is deleted. Creates a new log for this action.
     */
    protected function afterDelete()
    {
        $this->createLog(Actions::DELETE);
    }

    /**
     * Called after the model is updated. Creates a new log for this action.
     */
    protected function beforeUpdate()
    {
        // Get changed attributes
        $after  = $this->getDirty();

        // Get original values
        $before = array_intersect_key($this->getOriginal(), $after);

        // Create update event context
        $context = new UpdateLogContext();

        $context->before = $before;
        $context->after  = $after;

        $this->createLog(Actions::UPDATE, $context);
    }
}
