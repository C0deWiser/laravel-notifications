<?php

namespace Codewiser\Notifications;

use Codewiser\Notifications\Listeners\MarkSilentNotificationAsRead;
use Codewiser\Notifications\Listeners\NotificationMentions;
use Codewiser\Notifications\Models\DatabaseNotification;
use Illuminate\Notifications\Events\NotificationSent;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class NotificationsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any package services.
     */
    public function boot(): void
    {
        $this->publishesMigrations([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ]);

        Event::listen(NotificationSent::class, MarkSilentNotificationAsRead::class);
        Event::listen(NotificationSent::class, NotificationMentions::class);
    }
}