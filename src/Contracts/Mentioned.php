<?php

namespace Codewiser\Notifications\Contracts;

use Codewiser\Notifications\Builders\NotificationBuilder;
use Codewiser\Notifications\Models\DatabaseNotification;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * @property-read Collection|DatabaseNotification[] $mentions
 */
interface Mentioned
{
    /**
     * Notifications where model was mentioned.
     */
    public function mentions(): MorphToMany|NotificationBuilder;
}