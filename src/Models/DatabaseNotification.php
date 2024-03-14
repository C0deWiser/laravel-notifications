<?php

namespace Codewiser\Notifications\Models;

use Codewiser\Notifications\Casts\AsWebNotification;
use Codewiser\Notifications\Casts\WebNotification;
use Codewiser\Notifications\Events\NotificationWasRead;
use Codewiser\Notifications\Events\NotificationWasUnread;
use Codewiser\Notifications\Messages\DatabaseMessage;
use Codewiser\Notifications\Builders\NotificationBuilder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * @property string $id
 * @property string $type Notification class.
 *
 * @property WebNotification $data
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
        'data' => AsWebNotification::class
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

    public function markAsUnread(): void
    {
        parent::markAsUnread();

        event(new NotificationWasUnread($this));
    }

    public function prunable(): NotificationBuilder
    {
        return static::query()
            ->wherePrunable(was_read_before: Carbon::parse($this->prune_timeout));
    }

//    public function mentions(): HasMany
//    {
//        return $this->hasMany(Mention::class, 'notification_id');
//    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,

            'title'   => $this->data->title,
            'options'   => $this->data->options->toArray(),

            'read_at'    => $this->read_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
