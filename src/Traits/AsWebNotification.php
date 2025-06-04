<?php

namespace Codewiser\Notifications\Traits;

use Closure;
use Codewiser\Notifications\Enumerations\MessageLevel;
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
        } else {
            $body = null;
        }

        return $this->setOption('body', $body);
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
        return $this->setOption('dir', in_array($dir, ['ltr', 'rtl']) ? $dir : null);
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

        return $this->setOptionData('url', $url);
    }

    public function withoutAction(): static
    {
        $this->actionText = null;
        $this->actionUrl = null;

        return $this->setOptionData('url', null);
    }

    /**
     * @param string|MessageLevel $level
     *
     * @return $this
     */
    public function level($level): static
    {
        if ($level instanceof MessageLevel) {
            $level = $level->value;
        }

        if ($level == 'error') {
            $level = 'danger';
        }

        $this->level = $level;

        return $this->setOptionData('level', $level);
    }

    /**
     * A string containing the URL of an icon to be displayed in the notification.
     *
     * @param string|null $icon
     * @return $this
     */
    public function icon(?string $icon): static
    {
        return $this->setOption('icon', $icon);
    }

    /**
     * A string representing an identifying tag for the notification.
     *
     * @param string|null $tag
     * @return $this
     */
    public function tag(?string $tag): static
    {
        return $this->setOption('tag', $tag);
    }

    /**
     * The notification's language, as specified using a string representing a language tag according to RFC 5646.
     *
     * @param string|null $lang
     * @return $this
     */
    public function lang(?string $lang): static
    {
        return $this->setOption('lang', $lang);
    }

    public function silentIf(bool|Closure $condition): static
    {
        if ($condition instanceof Closure) {
            $condition = (boolean) call_user_func($condition);
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
        return $this->setOption('silent', $silent ?: null);
    }

    public function isSilent(): bool
    {
        return (bool) $this->getOption('silent');
    }

    /**
     * Arbitrary data that you want associated with the notification. This can be of any data type.
     *
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    protected function setOptionData(string $key, mixed $value): static
    {
        if (!is_null($value)) {
            Arr::set($this->data, 'options.data.' . $key, $value);
        } else {
            Arr::forget($this->data, 'options.data.' . $key);
        }

        return $this;
    }

    protected function getOptionData(string $key): mixed
    {
        return Arr::get($this->data, 'options.data.' . $key);
    }

    protected function setOption(string $key, mixed $value): static
    {
        if (!is_null($value)) {
            Arr::set($this->data, 'options.' . $key, $value);
        } else {
            Arr::forget($this->data, 'options.' . $key);
        }

        return $this;
    }

    protected function getOption(string $key): mixed
    {
        return Arr::get($this->data, 'options.' . $key);
    }

    public function requireInteraction(bool $require_interaction = true): static
    {
        return $this->setOption('requireInteraction', $require_interaction ?: null);
    }
}
