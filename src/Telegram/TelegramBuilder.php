<?php

namespace Codewiser\Notifications\Telegram;

use Codewiser\Notifications\Contracts\MessageContract;
use Codewiser\Notifications\Traits\AsSimpleMessage;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Support\Traits\Tappable;

class TelegramBuilder implements MessageContract, Renderable, Arrayable
{
    use Tappable, AsSimpleMessage;

    protected array $parameters = [];
    protected array $known = ['parse_mode', 'reply_markup'];

    public function __construct(ParseMode $parse_mode = ParseMode::markdown)
    {
        $this->parameters['parse_mode'] = $parse_mode->value;
    }

    public function set(string $key, mixed $value): static
    {
        if (!in_array($key, $this->known)) {
            return $this;
        }

        if ($value instanceof Arrayable) {
            $value = $value->toArray();
        }

        if (is_array($value)) {
            $value = json_encode($value, JSON_UNESCAPED_UNICODE);
        }

        $this->parameters[$key] = $value;

        return $this;
    }

    /**
     * Additional interface options.
     */
    public function replyMarkup(array|InlineKeyboard\InlineKeyboardMarkup $markup): static
    {
        return $this->set('reply_markup', $markup);
    }

    /**
     * Get the message content.
     */
    public function content(): string
    {
        return implode("\n\n", array_merge($this->introLines, $this->outroLines));
    }

    public function parameters(): array
    {
        if ($this->actionText && $this->actionUrl) {
            $this->replyMarkup(
                InlineKeyboard::markup(
                    [InlineKeyboard::button($this->actionText, $this->actionUrl)]
                )
            );
        }

        return array_filter($this->parameters);
    }


    /**
     * Render message preview.
     *
     * @return string
     */
    public function render(): string
    {
        $content = $this->content();

        if ($this->parameters['parse_mode'] == 'MarkdownV2') {
            $content = str($content)->markdown();
        }

        $template = '<style>
                    .telegram {
                        border: 1px solid ButtonBorder; 
                        background-color: ButtonFace;
                        width: 400px; 
                        border-radius: 8px; 
                        padding: 10px; 
                        font-family: sans-serif;
                        font-size: 12px;
                        box-shadow: 1px 1px 2px 1px ButtonFace;
                    }
                    .telegram header {
                        text-align: center;
                    }
                    .telegram article {
                        padding: 10px;
                        margin: 10px 0;
                        border-radius: 4px; 
                        border: 1px solid ButtonBorder;
                        background-color: Canvas;
                        color: CanvasText;
                        box-shadow: inset 1px 1px 2px 1px ButtonFace;
                        min-height: 400px;
                        display: flex;
                        justify-content: end;
                        flex-direction: column;
                    }
                    .telegram article p {
                        margin: 0 0 1em 0;
                    }
                    .telegram article p:last-child {
                        margin-bottom: 0;
                    }
                    .telegram footer {
                        display: flex;
                        justify-content: space-between;
                    }
                    .telegram footer button {
                        width: 80px;
                    }
                    .telegram footer input {
                        width: 300px; 
                    }
                    .telegram .inline_keyboard {
                        width: 100%;
                    }
                    .telegram .inline_keyboard td {
                        text-align: center;
                        border: 1px solid ButtonBorder; 
                        background-color: ButtonFace;
                        padding: 10px;
                    }
                    .telegram .inline_keyboard a {
                        font-size: 12px;
                        text-decoration: none;
                        color: ButtonText;
                    }
                    </style>';
        $template .= '<div class="telegram">
                        <header>Telegram</header>
                        <article>%content%</article>
                        <footer>
                            <input type="text" />
                            <button>Send</button>
                        </footer>
                    </div>';

        return str($template)
            ->replace('%content%', $content.$this->renderReplyMarkup())
            ->toString();
    }

    protected function renderReplyMarkup(): string
    {
        $this->parameters();

        $reply_markup = $this->parameters['reply_markup'] ?? [];
        if (is_string($reply_markup)) {
            $reply_markup = json_decode($reply_markup, true);
        }
        $inline_keyboard = $reply_markup['inline_keyboard'] ?? null;

        if ($inline_keyboard) {
            $rows = [];
            foreach ($inline_keyboard as $row) {
                $cells = [];
                foreach ($row as $cell) {
                    $text = $cell['text'] ?? null;
                    $url = $cell['url'] ?? null;
                    if ($text && $url) {
                        $cells[] = '<td><a href="'.$url.'" target="_blank">'.$text.'</a></td>';
                    } else {
                        $cells[] = '<td></td>';
                    }
                }
                $rows[] = '<tr>'.implode("\n", $cells).'</tr>';
            }
            $inline_keyboard = '<table class="inline_keyboard">'.implode("\n", $rows).'</table>';
        }

        return $inline_keyboard ?? '';
    }

    public function toArray(): array
    {
        return $this->parameters() + [
                'text' => $this->content()
            ];
    }
}
