<?php

namespace Codewiser\Notifications\Traits;

trait HasQuotation
{
    public function quotation($quotation): static
    {
        return $this->line(
        // Markdown syntax
            str('> '.$quotation)->toHtmlString()
        );
    }
}