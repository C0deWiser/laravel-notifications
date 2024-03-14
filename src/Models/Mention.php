<?php

namespace Codewiser\Notifications\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @deprecated ?
 */
class Mention extends Pivot
{
    public $incrementing = true;
    protected $table = 'notification_mention';
}