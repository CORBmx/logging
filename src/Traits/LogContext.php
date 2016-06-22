<?php

namespace Corb\Logging\Traits;

/**
 * The LogContext trait is used by models that store contextual data for specific
 * action logs. An example of this, is the update event log, that may store the
 * before and after state of the updated instance, so a LogContext is required.
 *
 * @author Jesús Barrera <jesus.barrera@corb.mx>
 */
trait LogContext
{
    /**
     * Get the ActivityLog this context belongs to.
     *
     * @author Jesús Barrera <jesus.barrera@corb.mx>
     *
     * @return BelongsTo Relation with the ActivityLog model
     */
    public function activityLog()
    {
        return $this->belongsTo('Corb\Logging\Models\ActivityLog');
    }
}
