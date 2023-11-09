<?php

namespace Codewiser\Notifications\Messages;

use Codewiser\Notifications\Contracts\MessageContract;
use Codewiser\Notifications\Enumerations\MessageLevel;
use Codewiser\Notifications\Traits\AsWebNotification;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Traits\Tappable;

class DatabaseMessage extends \Illuminate\Notifications\Messages\DatabaseMessage implements Arrayable, MessageContract
{
    use Tappable, AsWebNotification;

    /**
     * Notification cannot be marked as read by user.
     *
     * You may describe a reason why it is persistent.
     */
    public function persistent(bool|string $persistent = true): static
    {
        return $this->arbitraryData('persistent', $persistent);
    }

    /**
     * Check if notification is persistent and get a description (optional).
     */
    public function isPersistent(): bool|string
    {
        return Arr::get($this->data, 'options.data.persistent');
    }

    /**
     * @param MessageLevel $level
     *
     * @return $this
     */
    public function level($level): static
    {
        if (is_string($level)) {
            $this->arbitraryData('level', $level);
        }

        if ($level instanceof MessageLevel) {
            $this->arbitraryData('level', $level->value);
            $this->arbitraryData('priority', $level->priority());
        }

        return $this;
    }

    /**
     * Bind notification to a model.
     */
    public function bindTo(Model $model): static
    {
        return $this->arbitraryData("bind.{$model->getMorphClass()}", $model->getKey());
    }

    /**
     * Get Models mentioned in the Notification.
     */
    public function mentions(): Collection
    {
        $binds = Arr::get($this->data, 'options.data.bind');
        $morphMap = Relation::morphMap();
        $mentions = collect();

        foreach ($binds as $morph => $key) {

            $model = $morphMap[$morph] ?? $morph;

            if (class_exists($model) && method_exists($model, 'query')) {
                $model = $model::query()->find($key);
                if ($model) {
                    $mentions->add($model);
                }
            }
        }

        return $mentions;
    }

    /**
     * @deprecated use mentions()
     */
    public function bindedTo(): ?Model
    {
        return $this->mentions()->first();
    }

    public function toArray(): array
    {
        return $this->data;
    }
}
