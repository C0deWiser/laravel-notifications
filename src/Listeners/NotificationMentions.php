<?php

namespace Codewiser\Notifications\Listeners;

use Codewiser\Notifications\Contracts\Mentioned;
use Codewiser\Notifications\Models\DatabaseNotification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Events\NotificationSent;

class NotificationMentions
{
    public function handle(NotificationSent $event): void
    {
        if ($event->channel == 'database') {

            $notification = DatabaseNotification::query()
                ->find($event->notification->id);

            $notification?->data->mentions()
                ->filter(fn(Model $model) => $model instanceof Mentioned)
                ->each(fn(Mentioned $model) => $this->bind($model, $notification));
        }
    }

    public function bind(Mentioned $model, DatabaseNotification $notification): void
    {
        $model->mentions()->attach($notification);
    }
}