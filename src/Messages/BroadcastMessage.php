<?php

namespace Codewiser\Notifications\Messages;

use Codewiser\Notifications\Contracts\MessageContract;
use Codewiser\Notifications\Traits\AsWebNotification;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Support\Traits\Tappable;

/**
 * Format Broadcast Message as Web Notification.
 */
class BroadcastMessage extends \Illuminate\Notifications\Messages\BroadcastMessage implements Renderable, MessageContract
{
    use Tappable, AsWebNotification;

    public function __construct(array $data = [])
    {
        parent::__construct($data);
    }

    /**
     * Render message preview.
     */
    public function render(): string
    {
        $data = $this->data;

        $style = '<style>
                    .notification {
                        font-family: sans-serif; 
                        font-size: 12px;
                        color: ButtonText;
                        border: 1px solid ButtonBorder;
                        width: 320px; 
                        border-radius: 8px; 
                        padding: 10px; 
                        background-color: ButtonFace;
                        float: right;
                        display: flex;
                        box-shadow: 1px 1px 2px 1px ButtonFace;
                    }
                    .notification figure {
                        margin: 0 10px 0 0;
                        width: 40px;
                        height: 40px;
                        flex-shrink: 0;
                        border-radius: 50%;
                        text-align: center;
                        background: rgb(28, 169, 229);
                        background-size: 30px;
                    }
                    .notification header { 
                        font-weight: bold; 
                        margin: 0; 
                    }
                    .notification article {
                        margin: 1em 0 0 0;
                    }
                  </style>';

        $body = nl2br($data['options']['body'] ?? '');

        if ($body) {
            $body = '<article>' . $body . '</article>';
        }

        return $style .
            '<div class="notification">
                <figure></figure>
                <div>
                    <header>' . $data['title'] . '</header>
                    ' . $body . '
                </div>
            </div>';
    }
}
