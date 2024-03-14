<?php

namespace Codewiser\Notifications\Casts;

use Illuminate\Contracts\Support\Arrayable;

class WebNotificationOptions implements Arrayable
{
    /**
     * The direction in which to display the notification.
     */
    readonly public ?string $dir;

    /**
     * The notification's language, as specified using a string representing a language tag.
     */
    readonly public ?string $lang;

    /**
     * A string containing the URL of the image used to represent the notification
     * when there isn't enough space to display the notification itself.
     */
    readonly public ?string $badge;

    /**
     * A string representing the body text of the notification, which is displayed below the title.
     */
    readonly public ?string $body;

    /**
     * A string representing an identifying tag for the notification.
     */
    readonly public ?string $tag;

    /**
     * A string containing the URL of an icon to be displayed in the notification.
     */
    readonly public ?string $icon;

    /**
     * Arbitrary data that you want associated with the notification.
     */
    readonly public mixed $data;

    /**
     * Indicates that a notification should remain active until the user clicks or dismisses it,
     * rather than closing automatically.
     */
    readonly public bool $requireInteraction;

    /**
     * A boolean value specifying whether the notification is silent (no sounds or vibrations issued),
     * regardless of the device settings.
     */
    readonly public bool $silent;

    public function __construct(protected array $options)
    {
        $as_string = fn(string $attr): ?string => isset($this->options[$attr]) && is_string($this->options[$attr])
            ? $this->options[$attr] : null;

        $as_boolean = fn(string $attr): bool => $this->options[$attr] ?? false;

        $this->dir = $as_string('dir');
        $this->tag = $as_string('tag');
        $this->lang = $as_string('lang');
        $this->body = $as_string('body');
        $this->icon = $as_string('icon');
        $this->badge = $as_string('badge');

        $this->data = $this->options['data'] ?? null;
        $this->silent = $as_boolean('silent');
        $this->requireInteraction = $as_boolean('requireInteraction');
    }

    public function toArray(): array
    {
        return $this->options;
    }
}