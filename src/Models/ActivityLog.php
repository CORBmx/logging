<?php

namespace Corb\Logging\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model for the activity_logs table.
 *
 * @author Jesús Barrera <jesus.barrera@corb.mx>
 * @version 1.0.0
 * @since 1.0.0
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
     * @version 1.0.0
     * @since 1.0.0
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function loggeable()
    {
        return $this->morphTo();
    }

    /**
     * Get the responsible user of this log.
     *
     * @author Jesús Barrera <jesus.barrera@corb.mx>
     * @version 1.0.0
     * @since 1.0.0
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function responsible()
    {
        return $this->belongsTo(config('logging.user_model'), 'responsible_id');
    }

    /**
     * Dinamically defines a relationship with a context model if specified in
     * the configuration file.
     *
     * @author Jesús Barrera <jesus.barrera@corb.mx>
     * @version 1.0.0
     * @since 1.0.0
     *
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        $contexts_arr = config('logging.contexts');

        // If the method being called is a context key, define a relationship
        // with the context model. If it's not, use default Eloquent implementation.
        if (array_key_exists($method, $contexts_arr)) {
            return $this->hasOne($contexts_arr[$method]);
        } else {
            return parent::__call($method, $parameters);
        }
    }

    /**
     * Get a relationship. Overrides the default Illuminate\Database\Eloquent\Model
     * method to allow getting the relationship even if the method doesn't exist
     * in the model but it's defined as a context in the configuration file.
     *
     * @author Jesús Barrera <jesus.barrera@corb.mx>
     * @version 1.0.0
     * @since 1.0.0
     *
     * @param  string  $key
     * @return mixed
     */
    public function getRelationValue($key)
    {
        if ($this->relationLoaded($key)) {
            return $this->relations[$key];
        }

        if (method_exists($this, $key) || $this->contextIsDefined($key)) {
            return $this->getRelationshipFromMethod($key);
        }
    }

    /**
     * Determines if the given context is defined in the configuration file.
     *
     * @author Jesús Barrera <jesus.barrera@corb.mx>
     * @version 1.0.0
     * @since 1.0.0
     *
     * @param  string  $context
     * @return boolean
     */
    protected function contextIsDefined($context)
    {
        return array_key_exists($context, config('logging.contexts'));
    }
}
