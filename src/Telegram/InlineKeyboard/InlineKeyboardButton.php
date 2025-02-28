<?php

namespace Codewiser\Notifications\Telegram\InlineKeyboard;

use Illuminate\Contracts\Support\Arrayable;

/**
 * @deprecated
 */
class InlineKeyboardButton implements Arrayable
{
    /**
     * HTTP or tg:// URL to be opened when the button is pressed.
     * Links tg://user?id=<user_id> can be used
     * to mention a user by their ID without using a username,
     * if this is allowed by their privacy settings.
     */
    protected ?string $url = null;

    /**
     * @param string $text Label text on the button.
     */
    public function __construct(public string $text)
    {
        //
    }

    public function url(?string $url): static
    {
        $this->url = $url;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'text' => $this->text,
            'url' => $this->url,
        ];
    }
}
