<?php

namespace Corb\Logging\Models;

use Illuminate\Database\Eloquent\Model;
use Corb\Logging\Traits\LogContextTrait;

/**
 * The LogContextUpdate model abstracts the log_context_updates table, which stores
 * contextual data for the update event logs.
 *
 * @author Jesús Barrera <jesus.barrera@corb.mx>
 * @since 0.1.0
 * @version 0.1.0
 */
class LogContextUpdate extends Model
{
    use LogContextTrait;

    protected $table = 'log_context_updates';

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
