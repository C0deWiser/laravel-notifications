<?php

namespace Codewiser\Notifications\Telegram\InlineKeyboard;

use Illuminate\Contracts\Support\Arrayable;

/**
 * @deprecated
 */
class InlineKeyboard implements Arrayable
{
    /**
     * @var array<InlineKeyboardRow>
     */
    public array $rows = [];

    public function row(InlineKeyboardRow $row): static
    {
        $this->rows[] = $row;

        return $this;
    }

    public function toArray(): array
    {
        $rows = [];

        foreach ($this->rows as $row) {
            $rows[] = $row instanceof Arrayable ? $row->toArray() : $row;
        }

        return $rows;
    }
}
