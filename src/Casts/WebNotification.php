<?php

namespace Codewiser\Notifications\Casts;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class WebNotification implements Arrayable, \ArrayAccess
{
    /**
     * An options object containing any custom settings that you want to apply to the notification.
     */
    readonly public WebNotificationOptions $options;

    /**
     * @param  string  $title  Defines a title for the notification, which is shown at the top of the notification.
     * @param  array  $options
     */
    public function __construct(readonly public string $title, array $options)
    {
        $this->options = new WebNotificationOptions($options);
    }

    public function toArray(): array
    {
        return array_filter([
            'title'   => $this->title,
            'options' => $this->options->toArray(),
        ]);
    }

    /**
     * Get Models mentioned in the Notification.
     */
    public function mentions(): Collection
    {
        $data = $this->options->data;
        $mentions = collect();

        if (is_array($data)) {
            $binds = $data['bind'] ?? [];

            foreach ($binds as $morph => $key) {
                $model = Relation::getMorphedModel($morph) ?? $morph;

                if (class_exists($model) && method_exists($model, 'query')) {
                    $model = $model::query()->find($key);
                    if ($model) {
                        $mentions->add($model);
                    }
                }
            }
        }

        return $mentions;
    }

    /**
     * Check if notification is persistent and get a description (optional).
     */
    public function isPersistent(): bool|string
    {
        $data = $this->options->data;

        return $data['persistent'] ?? false;
    }

    public function offsetExists(mixed $offset): bool
    {
        $data = $this->options->data;

        return isset($data[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        $data = $this->options->data;

        return $data[$offset] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        //
    }

    public function offsetUnset(mixed $offset): void
    {
        //
    }
}