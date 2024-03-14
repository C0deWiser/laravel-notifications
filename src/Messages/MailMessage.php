<?php

namespace Codewiser\Notifications\Messages;

use Codewiser\Notifications\Contracts\MessageContract;
use Codewiser\Notifications\MarkdownTable;
use Codewiser\Notifications\Traits\HasQuotation;
use Illuminate\Support\Traits\Tappable;

class MailMessage extends \Illuminate\Notifications\Messages\MailMessage implements MessageContract
{
    use Tappable, HasQuotation;

    public function table(\Closure $builder): static
    {
        return $this->line(call_user_func($builder, MarkdownTable::make())->render());
    }
}
