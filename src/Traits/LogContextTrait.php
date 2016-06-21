<?php

namespace Corb\Logging\Traits;

/**
 * The LogContextTrait is used by models that store contextual data for specific
 * actions logs. An example of this, is the update event log, that may store the
 * before and after state of the updated instance, so a LogContext is required.
 *
 * @author Jesús Barrera <jesus.barrera@corb.mx>
 * @since 0.1.0
 * @version 0.1.0
 */
trait LogContextTrait {
    /**
     * Get the ActivityLog this context belongs to.
     *
     * @author Jesús Barrera <jesus.barrera@corb.mx>
     * @since 0.1.0
     * @version 0.1.0
     *
     * @return BelongsTo Relation with the ActivityLog model
     */
    public function activityLog()
    {
        return $this->belongsTo('Corb\Logging\Models\ActivityLog');
    }
}
