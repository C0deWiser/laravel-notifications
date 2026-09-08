<?php

namespace Codewiser\Notifications\Events;

use Codewiser\Notifications\Models\DatabaseNotification;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * The frontend should mark the notification as unread too.
 */
class DatabaseNotificationWasUnread extends DatabaseNotificationEvent
{
    public function broadcastAs(): string
    {
        return 'notification.unread';
    }
}
