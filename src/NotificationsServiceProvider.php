<?php

namespace Codewiser\Notifications;

use Codewiser\Notifications\Console\NotificationJsonCommand;
use Codewiser\Notifications\Console\NotificationMentionsCommand;
use Codewiser\Notifications\Listeners\MarkSilentNotificationAsRead;
use Codewiser\Notifications\Listeners\NotificationMentions;
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
        if ($this->app->runningInConsole()) {
            $this->commands([
                NotificationJsonCommand::class,
                NotificationMentionsCommand::class
            ]);
        }

        Event::listen(NotificationSent::class, MarkSilentNotificationAsRead::class);

        Event::listen(NotificationSent::class, NotificationMentions::class);
    }
}