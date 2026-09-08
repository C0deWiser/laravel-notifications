<?php

namespace Codewiser\Notifications\Listeners;

use Codewiser\Notifications\Casts\WebNotification;
use Codewiser\Notifications\Contracts\Mentionable;
use Codewiser\Notifications\Models\DatabaseNotification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Notifications\Events\NotificationSent;
use Illuminate\Support\Collection;

class NotificationMentions
{
    public function handle(NotificationSent $event): void
    {
        if ($event->channel == 'database') {

            $notification = DatabaseNotification::query()->find($event->notification->id);

            if ($notification) {
                $this->mentions($notification->data)
                    ->filter(fn(array $mention) => $mention[0] instanceof Mentionable)
                    ->each(function (array $mention) use ($notification) {
                        [$model, $pivot] = $mention;

                        $model->mentions()->attach($notification, $pivot);
                    });
            }
        }
    }

    /**
     * Get models mentioned in the notification, paired with their pivot attribute values.
     *
     * @return Collection<int, array{Model, array<string, mixed>}>
     */
    private function mentions(WebNotification $notification): Collection
    {
        $data = $notification->options->data;
        $mentions = collect();

        if (is_array($data)) {
            // Data of a just-sent notification is always in the 'attached' format.
            $binds = $data['attached'] ?? [];
            $pivots = $data['pivot_values'] ?? [];

            foreach ($binds as $morph => $keys) {

                $model = Relation::getMorphedModel($morph) ?? $morph;

                if (class_exists($model) && method_exists($model, 'query')) {
                    $models = $model::query()->find($keys);
                    if ($models) {
                        foreach ($models as $item) {
                            $mentions->push([$item, $pivots[$morph][$item->getKey()] ?? []]);
                        }
                    }
                }
            }
        }

        return $mentions;
    }
}