<?php

namespace Codewiser\Notifications\Models;

use Codewiser\Notifications\Casts\AsDatabaseMessage;
use Codewiser\Notifications\Events\NotificationWasRead;
use Codewiser\Notifications\Messages\DatabaseMessage;
use Codewiser\Notifications\Builders\NotificationBuilder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $type Notification class.
 *
 * @property DatabaseMessage $data
 * @property null|Carbon $read_at
 * @property null|Carbon $created_at
 * @property null|Carbon $updated_at
 *
 * @property-read Model $notifiable
 *
 * @method static NotificationBuilder query()
 */
class DatabaseNotification extends \Illuminate\Notifications\DatabaseNotification
{
    use Prunable;

    /**
     * Prune read messages after timeout.
     */
    protected string $prune_timeout = '-1 month';

    protected $casts = [
        'data' => AsDatabaseMessage::class
    ];

    public function newEloquentBuilder($query): Builder
    {
        return new NotificationBuilder($query);
    }

    public function markAsRead(): void
    {
        parent::markAsRead();

        event(new NotificationWasRead($this));
    }

    public function prunable(): NotificationBuilder
    {
        return static::query()
            ->wherePrunable(was_read_before: Carbon::parse($this->prune_timeout));
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,

            'title'   => $this->data->toArray()['title'] ?? '',
            'options'   => $this->data->toArray()['options'] ?? [],

            'read_at'    => $this->read_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
