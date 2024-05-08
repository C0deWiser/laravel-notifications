<?php

namespace Tests;

use Codewiser\Notifications\Messages\TelegramEditMessage;
use Codewiser\Notifications\Messages\TelegramMessage;
use PHPUnit\Framework\TestCase;

class TelegramMessageTest extends TestCase
{
    public function testTelegramMessage()
    {
        $m = new TelegramMessage();
        $m
            ->line('Hello')
            ->silently()
            ->protected()
            ->withoutPreview()
            ->replyTo(123);

        $this->assertEquals('Hello', $m->toArray()['text']);
        $this->assertEquals('MarkdownV2', $m->toArray()['parse_mode']);
        $this->assertTrue($m->toArray()['disable_notification']);
        $this->assertTrue($m->toArray()['protect_content']);
        $this->assertEquals('{"is_disabled":true}', $m->toArray()['link_preview_options']);
        $this->assertEquals('{"message_id":123}', $m->toArray()['reply_parameters']);
    }

    public function testTelegramEditMessage()
    {
        $m = new TelegramEditMessage(123);

        $this->assertEquals('MarkdownV2', $m->toArray()['parse_mode']);
        $this->assertEquals(123, $m->toArray()['message_id']);
    }
}
