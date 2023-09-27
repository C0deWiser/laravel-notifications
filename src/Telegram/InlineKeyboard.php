<?php

namespace Codewiser\Notifications\Telegram;

use Codewiser\Notifications\Telegram\InlineKeyboard\InlineKeyboardButton;
use Codewiser\Notifications\Telegram\InlineKeyboard\InlineKeyboardMarkup;
use Codewiser\Notifications\Telegram\InlineKeyboard\InlineKeyboardRow;

class InlineKeyboard
{
    /**
     * @param InlineKeyboardRow|array<InlineKeyboardButton> ...$rows
     */
    public static function markup(...$rows): InlineKeyboardMarkup
    {
        $markup = new InlineKeyboardMarkup();

        foreach ($rows as $row) {
            if (is_array($row)) {
                $keyboardRow = (new InlineKeyboardRow)->buttons($row);
            } else {
                $keyboardRow = $row;
            }
            $markup->keyboard->row($keyboardRow);
        }

        return $markup;
    }

    /**
     * @param InlineKeyboardButton ...$buttons
     */
    public static function row(...$buttons): InlineKeyboardRow
    {
        $keyboardRow = new InlineKeyboardRow();

        foreach ($buttons as $button) {
            $keyboardRow->button($button);
        }

        return $keyboardRow;
    }

    public static function button(string $text, string $url = null): InlineKeyboardButton
    {
        return (new InlineKeyboardButton($text))->url($url);
    }
}
