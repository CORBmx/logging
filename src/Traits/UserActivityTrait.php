<?php

namespace Corb\Logging\Traits;

trait UserActivityTrait
{
    /**
     * Get the logs that this user is responsible of.
     *
     * @author Jesús Barrera <jesus.barrera@corb.mx>
     * @since 0.1.0
     * @version 0.1.0
     */
    public function activity() {
        return $this->hasMany('Corb\Logging\Models\ActivityLog', 'responsible_id');
    }
}
