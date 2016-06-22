<?php

namespace Corb\Logging\Traits;

trait UserActivity
{
    /**
     * Get the logs that this user is responsible of.
     *
     * @author Jesús Barrera <jesus.barrera@corb.mx>
     */
    public function activity() {
        return $this->hasMany('Corb\Logging\Models\ActivityLog', 'responsible_id');
    }
}
