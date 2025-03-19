<?php

namespace Codewiser\Notifications\Models;

use Codewiser\Notifications\Builders\NotificationBuilder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphPivot;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class NotificationMention extends MorphPivot
{
    protected $table = 'notification_mention';

    public function notification(): BelongsTo|NotificationBuilder
    {
        return $this->belongsTo(DatabaseNotification::class, 'notification_id');
    }

    public function mentionable(): MorphTo
    {
        return $this->morphTo();
    }
}