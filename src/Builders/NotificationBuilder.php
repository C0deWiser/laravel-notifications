<?php

namespace Codewiser\Notifications\Builders;

use Codewiser\Notifications\Models\DatabaseNotification;

/**
 * @extends \Illuminate\Database\Eloquent\Builder<DatabaseNotification>
 */
class NotificationBuilder extends \Illuminate\Database\Eloquent\Builder
{
    /**
     * Scope a query to only include read notifications.
     */
    public function whereRead(): static
    {
        return $this->whereNotNull('read_at');
    }

    /**
     * Mark scoped notifications as read.
     */
    public function markAsRead(): bool
    {
        return $this->whereUnread()->each(
            fn(DatabaseNotification $notification) => $notification->markAsRead()
        );
    }

    /**
     * Scope a query to only include unread notifications.
     */
    public function whereUnread(): static
    {
        return $this->whereNull('read_at');
    }

    /**
     * Scope a query to only include prunable notifications.
     */
    public function wherePrunable(\DateTimeInterface $was_read_before): static
    {
        return $this->where(fn(self $builder) => $builder
            // Missing notifiable
            ->whereDoesntHave('notifiable')
            // Notification was read
            ->orWhere('read_at', '<', $was_read_before));
    }

    /**
     * Scope a query to only include notifications of given type(s).
     */
    public function whereType(mixed $types): static
    {
        $types = is_array($types) ? $types : func_get_args();

        foreach ($types as $i => $type) {
            if (is_object($type)) {
                $types[$i] = get_class($type);
            }
        }

        // If no types given — scope nothing.
        return $this->whereIn('type', $types ?: [0]);
    }

    /**
     * Scope a query to only include notifications mentioned to a given class or model.
     *
     * @param  class-string<\Illuminate\Database\Eloquent\Model>|\Illuminate\Database\Eloquent\Model  $classOrObject
     *
     * @return $this
     */
    public function whereMentioned(string|\Illuminate\Database\Eloquent\Model $classOrObject): static
    {
        if (is_string($classOrObject)) {
            $alias = array_search($classOrObject, \Illuminate\Database\Eloquent\Relations\Relation::$morphMap, strict: true) ?: $classOrObject;
        } else {
            $alias = $classOrObject->getMorphClass();
        }

        return is_string($classOrObject)
            ? $this->whereJsonContainsKey("data->options->data->bind->$alias")
            : $this->where("data->options->data->bind->$alias", $classOrObject->getKey());
    }

    /**
     * @deprecated use whereMentioned()
     */
    public function whereBindedTo(string|\Illuminate\Database\Eloquent\Model $classOrObject): static
    {
        return $this->whereMentioned($classOrObject);
    }

    /**
     * Urgent first.
     */
    public function orderByPriority(): static
    {
        return $this->orderByDesc('data->options->data->priority');
    }

    /**
     * Unread first.
     */
    public function orderByRead(): static
    {
        return $this->orderByRaw('read_at IS NULL DESC');
    }

    /**
     * Scope a query with notifiable.
     */
    public function whereNotifiable(\Illuminate\Database\Eloquent\Model $notifiable): static
    {
        return $this->whereMorphedTo('notifiable', $notifiable);
    }
}
