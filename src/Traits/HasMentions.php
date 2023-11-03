<?php

namespace Codewiser\Notifications\Traits;

use Codewiser\Notifications\Builders\NotificationBuilder;
use Codewiser\Notifications\Models\DatabaseNotification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * For Models that are mentioned in Notifications.
 * @mixin Model
 */
trait HasMentions
{
    /**
     * Notifications where Model was mentioned.
     */
    public function mentions(): HasMany|NotificationBuilder
    {
        return $this->hasMany(
            DatabaseNotification::class,
            "data->options->data->bind->{$this->getMorphClass()}"
        );
    }
}
