<?php

namespace Codewiser\Notifications\Contracts;

use Codewiser\Notifications\Builders\NotificationBuilder;
use Codewiser\Notifications\Models\DatabaseNotification;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * @deprecated Use Mentionable instead.
 */
interface Mentioned extends Mentionable
{
    //
}