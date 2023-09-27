<?php

namespace Codewiser\Notifications\Telegram\InlineKeyboard;

use Illuminate\Contracts\Support\Arrayable;

class InlineKeyboardMarkup implements Arrayable
{
    public InlineKeyboard $keyboard;

    public function __construct()
    {
        $this->keyboard = new InlineKeyboard();
    }

    public function toArray(): array
    {
        return [
            'inline_keyboard' => $this->keyboard->toArray()
        ];
    }
}
