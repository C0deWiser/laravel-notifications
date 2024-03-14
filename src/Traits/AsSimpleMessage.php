<?php

namespace Codewiser\Notifications\Traits;

use Codewiser\Notifications\Enumerations\MessageLevel;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Notifications\Action;
use Illuminate\Support\Traits\Conditionable;

trait AsSimpleMessage
{
    use Conditionable, HasQuotation;

    /**
     * The "level" of the notification (info, success, error).
     *
     * @var string
     */
    public $level = 'info';

    /**
     * The "intro" lines of the notification.
     *
     * @var array
     */
    public $introLines = [];

    /**
     * The "outro" lines of the notification.
     *
     * @var array
     */
    public $outroLines = [];

    /**
     * The text / label for the action.
     *
     * @var string
     */
    public $actionText = '';

    /**
     * The action URL.
     *
     * @var string
     */
    public $actionUrl = '';

    /**
     * The subject of the notification.
     *
     * @var string
     */
    public $subject = '';

    public function subject($subject): static
    {
        $this->subject = $subject;

        return $this;
    }

    public function success(): static
    {
        return $this->level('success');
    }

    /**
     * @param string|MessageLevel $level
     *
     * @return $this
     */
    public function level($level): static
    {
        if ($level instanceof MessageLevel) {
            $level = $level->value;
        }

        $this->level = $level;

        return $this;
    }

    public function error(): static
    {
        return $this->level('error');
    }

    public function lineIf($boolean, $line): static
    {
        if ($boolean) {
            return $this->line($line);
        }

        return $this;
    }

    public function line($line): static
    {
        return $this->with($line);
    }

    public function with($line): static
    {
        if ($line instanceof Action) {
            $this->action($line->text, $line->url);
        } elseif (!$this->actionText) {
            $this->introLines[] = $this->formatLine($line);
        } else {
            $this->outroLines[] = $this->formatLine($line);
        }

        return $this;
    }

    public function action($text, $url): static
    {
        $this->actionText = $text;
        $this->actionUrl = $url;

        return $this;
    }

    /**
     * Format the given line of text.
     *
     * @param array|string|Htmlable $line
     *
     * @return Htmlable|string
     */
    protected function formatLine(Htmlable|array|string $line): Htmlable|string
    {
        if ($line instanceof Htmlable) {
            return $line;
        }

        if (is_array($line)) {
            return implode(' ', array_map('trim', $line));
        }

        return trim(implode(' ', array_map('trim', preg_split('/\\r\\n|\\r|\\n/', $line ?? ''))));
    }

    public function linesIf($boolean, $lines): static
    {
        if ($boolean) {
            return $this->lines($lines);
        }

        return $this;
    }

    public function lines($lines): static
    {
        foreach ($lines as $line) {
            $this->line($line);
        }

        return $this;
    }
}
