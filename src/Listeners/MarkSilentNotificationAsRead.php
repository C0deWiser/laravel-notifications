<?php

namespace Codewiser\Notifications\Listeners;

use Codewiser\Notifications\Events\DatabaseNotificationWasRead;
use Codewiser\Notifications\Models\DatabaseNotification;
use Illuminate\Notifications\Events\NotificationSent;

/**
 * If database notification is silent,
 * we should mark it as read immediately.
 *
 * This fires broadcast event.
 *
 * @see DatabaseNotificationWasRead
 */
class MarkSilentNotificationAsRead
{
    public function handle(NotificationSent $event): void
    {
        if ($event->channel == 'database') {

            $notification = DatabaseNotification::query()
                ->find($event->notification->id);

            if ($notification?->data->options->silent) {
                $notification->markAsRead();
            }
        }
    }
}
