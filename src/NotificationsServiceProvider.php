<?php

namespace Codewiser\Notifications;

use Codewiser\Notifications\Console\NotificationJsonCommand;
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
            ]);
        }
    }
}