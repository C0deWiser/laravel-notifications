<?php

namespace Codewiser\Notifications\Messages;

use Codewiser\Notifications\Builders\NotificationBuilder;
use Codewiser\Notifications\Contracts\MessageContract;
use Codewiser\Notifications\Traits\AsWebNotification;
use Codewiser\Notifications\Enumerations\MessageLevel;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Traits\Tappable;

class DatabaseMessage extends \Illuminate\Notifications\Messages\DatabaseMessage implements Arrayable, MessageContract
{
    use Tappable, AsWebNotification;

    public Model $discussable;

    /**
     * Notification cannot be marked as read by user.
     */
    public function persistent(bool $persistent = true): static
    {
        return $this->arbitraryData('persistent', $persistent);
    }

    public function isPersistent(): bool
    {
        return (bool)Arr::get($this->data, 'options.data.persistent');
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
        $type = NotificationBuilder::morph($model);

        return $this->arbitraryData("bind.$type", $model->getKey());
    }

    public function bindedTo(): ?Model
    {
        $binds = Arr::get($this->data, 'options.data.bind');
        foreach ($binds as $morph => $key) {
            $model = NotificationBuilder::unmorph($morph);
            if (class_exists($model) && method_exists($model, 'query')) {
                $model = $model::query()->find($key);
                if ($model) {
                    return $model;
                }
            }
        }
        return null;
    }

    public function toArray(): array
    {
        return $this->data;
    }
}
