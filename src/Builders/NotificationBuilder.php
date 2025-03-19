<?php

namespace Codewiser\Notifications\Builders;

use Codewiser\Notifications\Models\DatabaseNotification;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

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
     * @param  class-string<Model>|Model|array<array-key,class-string<Model>|Model|\Closure>  $relations
     *
     * @return $this
     */
    public function whereMentioned(mixed $relations, \Closure $callback = null): static
    {
        $relations = is_array($relations) ? $relations : func_get_args();

        foreach ($relations as $key => $value) {

            if (is_string($key) && is_callable($value)) {
                $callback = $value;
                $value = $key;
            } else {
                $callback = null;
            }

            $this->whereHas('mentions', fn(Builder $builder) => $builder
                ->when($callback,
                    // Constrain with a callback
                    fn(Builder $builder) => $builder->whereHasMorph('mentionable', $value, $callback),
                    // Value is a model or a class-name — both works well
                    fn(Builder $builder) => $builder->whereMorphedTo('mentionable', $value)
                )
            );
        }

        return $this;
    }

    /**
     * @deprecated use whereMentioned()
     */
    public function whereBindedTo(mixed $relations, \Closure $callback = null): static
    {
        return $this->whereMentioned($relations, $callback);
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
    public function whereNotifiable(Model $notifiable): static
    {
        return $this->whereMorphedTo('notifiable', $notifiable);
    }
}
