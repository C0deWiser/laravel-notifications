<?php

namespace Codewiser\Notifications\Messages;

use Codewiser\Notifications\Contracts\MessageContract;
use Codewiser\Notifications\Traits\AsWebNotification;
use Illuminate\Database\Eloquent\Relations\Relation;
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
        $type = array_search(get_class($model), Relation::morphMap());
        $type = $type !== false ? $type : get_class($model);
        $id = $model->getKey();

        $this->arbitraryData(class_basename($type), $id);

        return $this->arbitraryData('bind', "$type/$id");
    }

    public function bindedTo(): ?string
    {
        return Arr::get($this->data, 'options.data.bind');
    }

    public function toArray(): array
    {
        return $this->data;
    }
}
