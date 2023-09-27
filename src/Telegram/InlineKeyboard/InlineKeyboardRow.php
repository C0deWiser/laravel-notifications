<?php

namespace Codewiser\Notifications\Telegram\InlineKeyboard;

use Illuminate\Contracts\Support\Arrayable;

class InlineKeyboardRow implements Arrayable
{
    /**
     * @var array<InlineKeyboardButton>
     */
    protected array $buttons = [];

    /**
     * @param array<InlineKeyboardButton> $buttons
     */
    public function buttons(array $buttons): static
    {
        $this->buttons = $buttons;

        return $this;
    }

    public function button(InlineKeyboardButton $button): static
    {
        $this->buttons[] = $button;

        return $this;
    }

    public function toArray(): array
    {
        $buttons = [];

        foreach ($this->buttons as $button) {
            $buttons[] = $button->toArray();
        }

        return $buttons;
    }
}
