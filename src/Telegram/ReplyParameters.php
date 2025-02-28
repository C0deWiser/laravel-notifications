<?php

namespace Codewiser\Notifications\Telegram;

use Illuminate\Contracts\Support\Arrayable;

/**
 * @deprecated
 */
class ReplyParameters implements Arrayable
{
    /**
     * @param  int  $message_id Identifier of the message that will be replied to in the current chat, or in the chat chat_id if it is specified
     * @param  int|string|null  $chat_id If the message to be replied to is from a different chat, unique identifier for the chat or username of the channel
     * @param  bool|null  $allow_sending_without_reply Pass True if the message should be sent even if the specified message to be replied to is not found
     * @param  string|null  $quote Quoted part of the message to be replied to
     * @param  string|null  $quote_parse_mode Mode for parsing entities in the quote
     * @param  array|null  $quote_entities A list of special entities that appear in the quote
     * @param  int|null  $quote_position Position of the quote in the original message in UTF-16 code units
     */
    public function __construct(
        public int $message_id,
        public null|int|string $chat_id = null,
        public ?bool $allow_sending_without_reply = null,
        public ?string $quote = null,
        public ?string $quote_parse_mode = null,
        public ?array $quote_entities = null,
        public ?int $quote_position = null,
    )
    {
        //
    }

    public function toArray(): array
    {
        return array_filter([
            'message_id' => $this->message_id,
            'chat_id' => $this->chat_id,
            'allow_sending_without_reply' => $this->allow_sending_without_reply,
            'quote' => $this->quote,
            'quote_parse_mode' => $this->quote_parse_mode,
            'quote_entities' => $this->quote_entities ? json_encode($this->quote_entities, JSON_UNESCAPED_UNICODE) : null,
            'quote_position' => $this->quote_position,
        ]);
    }
}