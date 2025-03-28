<?php

namespace Codewiser\Notifications\Traits;

use Closure;
use Codewiser\Notifications\Builders\NotificationBuilder;
use Codewiser\Notifications\Models\DatabaseNotification;
use Codewiser\Notifications\Models\NotificationMention;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * For Models that are mentioned in Notifications.
 *
 * @mixin Model
 */
trait HasMentions
{
    public function mentions(): MorphToMany|NotificationBuilder
    {
        return $this->morphToMany(
            DatabaseNotification::class, 'mentionable', NotificationMention::class,
            relatedPivotKey: 'notification_id'
        );
    }

    /**
     * Load user's unread notifications about this model.
     *
     * @param  Closure(MorphToMany|NotificationBuilder):MorphToMany|NotificationBuilder  $callback
     */
    public function loadUnreadMentions(null|Authenticatable|Model $notifiable, ?Closure $callback = null): static
    {
        if ($notifiable) {
            $this
                ->load([
                    'mentions' => fn(MorphToMany|NotificationBuilder $builder) => $builder
                        ->when($callback, fn(Builder $builder) => $builder->where($callback))
                        ->whereNotifiable($notifiable)
                        ->with('notifiable')
                        ->whereUnread()
                ])
                ->loadCount([
                    'mentions' => fn(MorphToMany|NotificationBuilder $builder) => $builder
                        ->when($callback, fn(Builder $builder) => $builder->where($callback))
                        ->whereNotifiable($notifiable)
                        ->whereUnread()
                ]);
        }

        return $this;
    }

    /**
     * Load user's unread notifications about this model.
     *
     * @param  Closure(MorphToMany|NotificationBuilder):MorphToMany|NotificationBuilder  $callback
     *
     * @deprecated
     */
    public function loadUnreadMentionsCount(null|Authenticatable|Model $notifiable, ?Closure $callback = null): static
    {
        if ($notifiable) {
            $this->loadCount([
                'mentions' => function (MorphToMany|NotificationBuilder $builder) use ($notifiable, $callback) {
                    if ($callback) {
                        call_user_func($callback, $builder);
                    }

                    return $builder
                        ->whereNotifiable($notifiable)
                        ->whereUnread();
                }
            ]);
        }

        return $this;
    }

    /**
     * @param  Builder  $builder
     * @param  Model|null  $authenticated
     * @param  Closure(NotificationBuilder):NotificationBuilder  $callback
     *
     * @return void
     */
    public function scopeWithUnreadMentions(Builder $builder, ?Model $authenticated, ?\Closure $callback = null): void
    {
        $builder
            ->when($authenticated, fn(\Illuminate\Database\Eloquent\Builder $builder) => $builder
                ->withCount([
                    'mentions' => fn(NotificationBuilder $builder) => $builder
                        ->when($callback, fn(NotificationBuilder $builder) => $builder->where($callback))
                        ->whereNotifiable($authenticated)
                        ->with('notifiable')
                        ->whereUnread(),
                ])
            );
    }
}
