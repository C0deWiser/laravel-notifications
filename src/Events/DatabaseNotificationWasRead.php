<?php

namespace Codewiser\Notifications\Events;

use Codewiser\Notifications\Models\DatabaseNotification;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * The frontend should mark the notification as read too.
 */
class DatabaseNotificationWasRead extends DatabaseNotificationEvent
{
    public function broadcastAs(): string
    {
        return 'notification.read';
    }
}
