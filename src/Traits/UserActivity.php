<?php

namespace Corb\Logging\Traits;

/**
 * This trait should be used in the application user model. This will allow to
 * get the user activity history, this is, all the logs where the user appears
 * as responsible.
 *
 * @author Jesús Barrera <jesus.barrera@corb.mx>
 * @since 1.0.0
 * @version 1.0.0
 */
trait UserActivity
{
    /**
     * Get the logs that the user is responsible of.
     *
     * @author Jesús Barrera <jesus.barrera@corb.mx>
     * @since 1.0.0
     * @version 1.0.0
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function activity() {
        return $this->hasMany('Corb\Logging\Models\ActivityLog', 'responsible_id');
    }
}
