<?php

namespace Codewiser\Notifications;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Traits\Conditionable;

class MarkdownTable implements Renderable
{
    use Conditionable;

    protected array $lines = [];

    public static function make(): static
    {
        return new static;
    }

    public function render(): HtmlString|string
    {
        return str(implode("\n", $this->lines))
            ->markdown()
            ->prepend('<div class="table">')
            ->append('</div>')
            ->toHtmlString();
    }

    public function rows(array $rows): static
    {
        foreach ($rows as $row) {
            $this->row($row);
        }

        return $this;
    }

    public function row(string|array $row): static
    {
        if (is_array($row)) {
            $row = '| ' . implode(' | ', $row) . ' |';
        }

        $this->lines[] = (string)$row;

        return $this;
    }
}
