<?php

namespace Codewiser\Notifications\Builders;

use Codewiser\Notifications\Models\DatabaseNotification as Model;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Carbon;

/**
 * @method Model getModel()
 * @method Model make(array $attributes = [])
 * @method Model create(array $attributes = [])
 * @method Model forceCreate(array $attributes)
 *
 * @method Model sole($columns = ['*'])
 *
 * @method Model find($id, $columns = ['*'])
 * @method Model findOr($id, $columns = ['*'], Closure $callback = null)
 * @method Model findOrNew($id, $columns = ['*'])
 * @method Model findOrFail($id, $columns = ['*'])
 * @method Collection|Model[] findMany($ids, $columns = ['*'])
 *
 * @method Model first($columns = ['*'])
 * @method Model firstOr($columns = ['*'], Closure $callback = null)
 * @method Model firstOrNew(array $attributes = [], array $values = [])
 * @method Model firstOrFail($columns = ['*'])
 * @method Model firstOrCreate(array $attributes = [], array $values = [])
 * @method Model firstWhere($column, $operator = null, $value = null, $boolean = 'and')
 *
 * @method Model updateOrCreate(array $attributes, array $values = [])
 *
 * @method Collection|Model[] get($columns = ['*'])
 */
class NotificationBuilder extends Builder
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
        return $this
            ->whereUnread()
            ->each(fn(Model $notification) => $notification->markAsRead());
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
    public function wherePrunable(Carbon $was_read_before): static
    {
        return $this
            ->where(fn(self $builder) => $builder
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

        return $this
            ->whereIn('type', $types);
    }

    /**
     * Scope a query to only include notifications mentioned to a given model.
     */
    public function whereMentioned(\Illuminate\Database\Eloquent\Model $model): static
    {
        return $this
            ->where("data->options->data->bind->{$model->getMorphClass()}", $model->getKey());
    }

    /**
     * @deprecated use whereMentioned()
     */
    public function whereBindedTo(\Illuminate\Database\Eloquent\Model $model): static
    {
        return $this->whereMentioned($model);
    }

    /**
     * Order a query by notification priority.
     */
    public function orderByPriority(): static
    {
        return $this
            ->orderBy('data->options->data->priority');
    }

    public function orderByRead(): static
    {
        return $this
            ->orderBy('read_at');
    }

    /**
     * Scope a query with notifiable.
     */
    public function whereNotifiable(\Illuminate\Database\Eloquent\Model $model): static
    {
        return $this
            ->whereMorphedTo('notifiable', $model);
    }
}
