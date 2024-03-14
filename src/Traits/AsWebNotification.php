<?php

namespace Codewiser\Notifications\Traits;

use Closure;
use Codewiser\Notifications\Traits\AsSimpleMessage;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * Format data as Web Notification
 *
 * @see https://developer.mozilla.org/en-US/docs/Web/API/Notification
 *
 * @property array{
 *     title: string,
 *     options: array{
 *          dir: string,
 *          lang: string,
 *          tag: string,
 *          icon: string,
 *          silent: boolean,
 *          requireInteraction: boolean,
 *          data: array,
 *     }} $data
 */
trait AsWebNotification
{
    use AsSimpleMessage {
        with as protected _with;
    }

    public function with($line): static
    {
        $this->_with($line);

        if ($this->introLines || $this->outroLines) {
            $body = str(implode("\n", array_merge($this->introLines, $this->outroLines)))
                ->markdown()
                ->stripTags()
                ->trim()
                ->toString();
            Arr::set($this->data, 'options.body', $body);
        } else {
            Arr::forget($this->data, 'options.body');
        }

        return $this;
    }

    /**
     * The direction in which to display the notification.
     * It defaults to auto, which just adopts the browser's language setting behavior,
     * but you can override that behavior by setting values of ltr and rtl
     * (although most browsers seem to ignore these settings.)
     *
     * @param string $dir
     * @return $this
     */
    public function dir(string $dir): static
    {
        if (in_array($dir, ['ltr', 'rtl'])) {
            Arr::set($this->data, 'options.dir', $dir);
        } else {
            Arr::forget($this->data, 'options.dir');
        }

        return $this;
    }

    /**
     * Set the title of the notification.
     *
     * @param string $title
     * @return $this
     */
    public function title(string $title): static
    {
        return $this->subject($title);
    }

    public function subject($subject): static
    {
        $this->subject = $subject;

        Arr::set($this->data, 'title', $this->subject ?? '');

        return $this;
    }

    public function action($text, $url): static
    {
        $this->actionText = $text;
        $this->actionUrl = $url;

        Arr::set($this->data, 'options.data.url', $this->actionUrl);

        return $this;
    }

    public function withoutAction(): static
    {
        $this->actionText = null;
        $this->actionUrl = null;

        Arr::forget($this->data, 'options.data.url');

        return $this;
    }

    /**
     * A string containing the URL of an icon to be displayed in the notification.
     *
     * @param string|null $icon
     * @return $this
     */
    public function icon(?string $icon): static
    {
        if ($icon) {
            Arr::set($this->data, 'options.icon', $icon);
        } else {
            Arr::forget($this->data, 'options.icon');
        }

        return $this;
    }

    /**
     * A string representing an identifying tag for the notification.
     *
     * @param string|null $tag
     * @return $this
     */
    public function tag(?string $tag): static
    {
        if ($tag) {
            Arr::set($this->data, 'options.tag', $tag);
        } else {
            Arr::forget($this->data, 'options.tag');
        }

        return $this;
    }

    /**
     * The notification's language, as specified using a string representing a language tag according to RFC 5646.
     *
     * @param string|null $lang
     * @return $this
     */
    public function lang(?string $lang): static
    {
        if ($lang) {
            Arr::set($this->data, 'options.lang', $lang);
        } else {
            Arr::forget($this->data, 'options.lang');
        }

        return $this;
    }

    public function silentIf(bool|Closure $condition): static
    {
        if ($condition instanceof Closure) {
            if (call_user_func($condition)) {
                return $this->silent();
            }
        }

        return $this->silent($condition);
    }

    /**
     * A boolean value specifying whether the notification is silent (no sounds or vibrations issued),
     * regardless of the device settings. The default is false, which means it won't be silent.
     *
     * @param bool $silent
     * @return $this
     */
    public function silent(bool $silent = true): static
    {
        if ($silent) {
            Arr::set($this->data, 'options.silent', true);
        } else {
            Arr::forget($this->data, 'options.silent');
        }

        return $this;
    }

    public function isSilent(): bool
    {
        return (bool)Arr::get($this->data, 'options.silent');
    }

    /**
     * Arbitrary data that you want associated with the notification. This can be of any data type.
     *
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    protected function arbitraryData(string $key, mixed $value): static
    {
        if (!is_null($value)) {
            Arr::set($this->data, 'options.data.' . $key, $value);
        } else {
            Arr::forget($this->data, 'options.data.' . $key);
        }

        return $this;
    }

    public function requireInteraction(bool $require_interaction = true): static
    {
        if ($require_interaction) {
            Arr::set($this->data, 'options.requireInteraction', true);
        } else {
            Arr::forget($this->data, 'options.requireInteraction');
        }

        return $this;
    }
}
