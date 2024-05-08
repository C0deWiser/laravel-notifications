<?php

namespace Codewiser\Notifications\Messages;

use Codewiser\Notifications\Telegram\ParseMode;
use Codewiser\Notifications\Telegram\TelegramBuilder;

class TelegramEditMessage extends TelegramBuilder
{
    protected array $parameters = [];
    protected array $known = [
        'message_id', 'inline_message_id', 'parse_mode', 'entities', 'link_preview_options', 'reply_markup'
    ];

    public function __construct(int $message_id, ParseMode $parse_mode = ParseMode::markdown)
    {
        parent::__construct($parse_mode);

        $this->parameters['message_id'] = $message_id;
    }
}
