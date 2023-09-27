<?php

namespace Codewiser\Notifications\Telegram;

enum ParseMode: string
{
    case markdown = 'MarkdownV2';
    case html = 'HTML';
}
