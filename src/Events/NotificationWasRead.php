<?php

namespace Codewiser\Notifications\Events;

use Codewiser\Notifications\Models\DatabaseNotification;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Frontend should mark a notification as read too.
 */
class NotificationWasRead implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public DatabaseNotification $notification)
    {
        //
    }

    public function broadcastWhen(): bool
    {
        return true;
    }

    public function broadcastWith(): array
    {
        return $this->notification->toArray();
    }

    public function broadcastOn(): array
    {
        $notifiable = $this->notification->notifiable;

        if (method_exists($notifiable, 'receivesBroadcastNotificationsOn')) {
            $route = $notifiable->receivesBroadcastNotificationsOn();
        } else {
            $type = str($notifiable::class)->replace('\\', '.');
            $route = $type.'.'.$notifiable->getKey();
        }


        return [
            new PrivateChannel($route),
        ];
    }
}
