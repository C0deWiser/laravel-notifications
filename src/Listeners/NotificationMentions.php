<?php

namespace Codewiser\Notifications\Listeners;

use Codewiser\Notifications\Contracts\Mentionable;
use Codewiser\Notifications\Events\DatabaseNotificationWasSent;
use Codewiser\Notifications\Models\DatabaseNotification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Events\NotificationSent;

class NotificationMentions
{
    public function handle(NotificationSent $event): void
    {
        if ($event->channel == 'database') {

            $notification = DatabaseNotification::query()->find($event->notification->id);

            if ($notification) {

                event(new DatabaseNotificationWasSent($notification));

                $notification->data->mentions()
                    ->filter(fn(Model $model) => $model instanceof Mentionable)
                    ->each(fn(Mentionable $model) => $this->bind($model, $notification));
            }
        }
    }

    public function bind(Mentionable $model, DatabaseNotification $notification): void
    {
        $model->mentions()->attach($notification);
    }
}