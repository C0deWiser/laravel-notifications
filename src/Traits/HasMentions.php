<?php

namespace Codewiser\Notifications\Traits;

use Codewiser\Notifications\Builders\HasManyJson;
use Codewiser\Notifications\Builders\NotificationBuilder;
use Codewiser\Notifications\Models\DatabaseNotification;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * For Models that are mentioned in Notifications.
 *
 * @mixin Model
 */
trait HasMentions
{
    public function hasManyJson($related, $foreignKey = null, $localKey = null): HasManyJson
    {
        $instance = $this->newRelatedInstance($related);

        $foreignKey = $foreignKey ?: $this->getForeignKey();

        $localKey = $localKey ?: $this->getKeyName();

        return $this->newHasManyJson(
            $instance->newQuery(), $this, $instance->getTable().'.'.$foreignKey, $localKey
        );
    }

    protected function newHasManyJson(Builder $query, Model $parent, $foreignKey, $localKey): HasManyJson
    {
        return new HasManyJson($query, $parent, $foreignKey, $localKey);
    }

    /**
     * Notifications where Model was mentioned.
     */
    public function mentions(): HasMany|NotificationBuilder
    {
        return $this->hasManyJson(
            DatabaseNotification::class,
            "data->options->data->bind->{$this->getMorphClass()}"
        );
    }

    /**
     * Load user's unread notifications about this model.
     */
    public function loadUnreadMentions(null|Authenticatable|Model $authenticatable): static
    {
        if ($authenticatable) {
            $this->load([
                'mentions' => fn(HasMany|NotificationBuilder $builder) => $builder
                    ->whereNotifiable($authenticatable)
                    ->whereUnread()
                    ->with('notifiable')
            ]);
        }

        return $this;
    }
}
