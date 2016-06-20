<?php

namespace Corb\Logging\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model for the activity_logs table.
 *
 * @author Jesús Barrera <jesus.barrera@corb.mx>
 * @since 0.1.0
 * @version 0.1.0
 */
class ActivityLog extends Model
{
    public $timestamps = false;

    protected $dates = ['created_at'];

    protected $table = 'activity_logs';

    protected $fillable = [
        'action',
        'responsible_id'
    ];

    /**
     * Get the loggeable model.
     *
     * @author Jesús Barrera <jesus.barrera@corb.mx>
     * @since 0.1.0
     * @version 0.1.0
     */
    public function loggeable()
    {
        return $this->morphTo();
    }

    /**
     * Get the responsible user of this log.
     *
     * @author Jesús Barrera <jesus.barrera@corb.mx>
     * @since 0.1.0
     * @version 0.1.0
     *
     * @return BelongsTo
     */
    public function responsible()
    {
        return $this->belongsTo('App\Models\Core\User', 'responsible_id');
    }

    /**
     * Gets the update log context.
     *
     * @author Jesús Barrera <jesus.barrera@corb.mx>
     * @since 0.1.0
     * @version 0.1.0
     *
     * @return HasOne
     */
    public function update_context()
    {
        return $this->hasOne('App\Models\LogContexts\LogContextUpdate');
    }
}
