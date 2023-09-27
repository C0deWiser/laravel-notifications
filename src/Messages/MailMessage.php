<?php

namespace Codewiser\Notifications\Messages;

use Codewiser\Notifications\Contracts\MessageContract;
use Illuminate\Support\Traits\Tappable;

class MailMessage extends \Illuminate\Notifications\Messages\MailMessage implements MessageContract
{
    use Tappable;
}
