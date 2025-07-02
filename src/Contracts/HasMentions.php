<?php

namespace Codewiser\Notifications\Contracts;

use Closure;
use Illuminate\Database\Eloquent\Model;

/**
 * Helper interface for a custom builder.
 *
 * @method $this withUnreadMentions(?Model $notifiable, ?Closure $callback = null)
 */
interface HasMentions
{

}
