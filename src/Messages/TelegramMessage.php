<?php

namespace Codewiser\Notifications\Messages;

use Codewiser\Notifications\Telegram\LinkPreviewOptions;
use Codewiser\Notifications\Telegram\ReplyParameters;
use Codewiser\Notifications\Telegram\TelegramBuilder;

/**
 * @deprecated
 */
class TelegramMessage extends TelegramBuilder
{
    protected array $known = [
        'business_connection_id', 'message_thread_id', 'parse_mode', 'entities', 'link_preview_options',
        'disable_notification', 'protect_content', 'reply_parameters', 'reply_markup'
    ];

    /**
     * Send message as a reply.
     *
     * @see https://core.telegram.org/bots/api#replyparameters
     */
    public function replyTo(int|array|ReplyParameters $message): static
    {
        $message = is_scalar($message) ? new ReplyParameters($message) : $message;

        return $this->set('reply_parameters', $message);
    }

    /**
     * Protects the contents of the sent message from forwarding and saving.
     */
    public function protected(bool $protect_content = true): static
    {
        return $this->set('protect_content', $protect_content);
    }

    /**
     * Sends the message silently. Users will receive a notification with no sound.
     */
    public function silently(bool $disable_notification = true): static
    {
        return $this->set('disable_notification', $disable_notification);
    }

    /**
     * Disables link previews for links in this message.
     *
     * @deprecated Use linkPreviewOptions()
     */
    public function withoutPreview(bool $disable_web_page_preview = true): static
    {
        return $this->linkPreviewOptions(new LinkPreviewOptions(is_disabled: $disable_web_page_preview));
    }

    /**
     * Link preview generation options for the message
     *
     * @see https://core.telegram.org/bots/api#linkpreviewoptions
     */
    public function linkPreviewOptions(array|LinkPreviewOptions $options): static
    {
        return $this->set('link_preview_options', $options);
    }
}
