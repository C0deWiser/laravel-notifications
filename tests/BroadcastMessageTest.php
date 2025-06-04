<?php

namespace Tests;

use Codewiser\Notifications\Enumerations\MessageLevel;
use Codewiser\Notifications\Messages\BroadcastMessage;
use Codewiser\Notifications\Messages\TelegramEditMessage;
use PHPUnit\Framework\TestCase;

class BroadcastMessageTest extends TestCase
{
    public function testLevel()
    {
        $message = new BroadcastMessage();
        $message
            ->subject('test')
            ->line('test line')
            ->action('test action', 'https://example.com')
            ->silent()
            ->requireInteraction()
            ->lang('ru')
            ->tag('tag')
            ->dir('ltr')
            ->icon('icon')
            ->error();

        $this->assertEquals('danger', $message->data['options']['data']['level']);
    }
}
