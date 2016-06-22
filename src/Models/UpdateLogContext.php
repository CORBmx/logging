<?php

namespace Corb\Logging\Models;

use Illuminate\Database\Eloquent\Model;
use Corb\Logging\Traits\LogContext;

/**
 * The LogContextUpdate model abstracts the log_context_updates table, which stores
 * contextual data for the update action logs.
 *
 * @author Jesús Barrera <jesus.barrera@corb.mx>
 */
class UpdateLogContext extends Model
{
    use LogContext;

    protected $table = 'update_log_contexts';

    protected $fillable = [
        'before',
        'after'
    ];

    protected $casts = [
        'before' => 'json',
        'after'  => 'json'
    ];

    public $timestamps = false;
}
