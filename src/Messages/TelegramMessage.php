<?php

namespace Codewiser\Notifications\Messages;

use Codewiser\Notifications\Contracts\MessageContract;
use Codewiser\Notifications\Telegram\InlineKeyboard;
use Codewiser\Notifications\Telegram\ParseMode;
use Codewiser\Notifications\Traits\AsSimpleMessage;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Support\Traits\Tappable;

class TelegramMessage implements MessageContract, Renderable, Arrayable
{
    use Tappable, AsSimpleMessage;

    protected array $reply_markup = [];
    protected ?bool $disable_web_page_preview = null;
    protected ?bool $disable_notification = null;
    protected ?bool $protect_content = null;

    public function __construct(protected ParseMode $parse_mode = ParseMode::markdown)
    {
        //
    }

    /**
     * Protects the contents of the sent message from forwarding and saving.
     */
    public function protected(bool $protect_content = true): static
    {
        $this->protect_content = $protect_content;

        return $this;
    }

    /**
     * Sends the message silently. Users will receive a notification with no sound.
     */
    public function silently(bool $disable_notification = true): static
    {
        $this->disable_notification = $disable_notification;

        return $this;
    }

    /**
     * Disables link previews for links in this message.
     */
    public function withoutPreview(bool $disable_web_page_preview = true): static
    {
        $this->disable_web_page_preview = $disable_web_page_preview;

        return $this;
    }

    /**
     * Additional interface options.
     */
    public function replyMarkup(array|InlineKeyboard\InlineKeyboardMarkup $markup): static
    {
        $this->reply_markup = is_array($markup) ? $markup : $markup->toArray();

        return $this;
    }

    /**
     * Get the message content.
     */
    public function content(): string
    {
        return implode("\n", array_merge($this->introLines, $this->outroLines));
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

        $parameters = [
            'parse_mode' => $this->parse_mode->value,
        ];

        if ($this->reply_markup) {
            $parameters['reply_markup'] = json_encode($this->reply_markup, JSON_UNESCAPED_UNICODE);
        }

        if (!is_null($this->disable_web_page_preview)) {
            $parameters['disable_web_page_preview'] = $this->disable_web_page_preview;
        }

        if (!is_null($this->disable_notification)) {
            $parameters['disable_notification'] = $this->disable_notification;
        }

        if (!is_null($this->protect_content)) {
            $parameters['protect_content'] = $this->protect_content;
        }

        return $parameters;
    }


    /**
     * Render message preview.
     *
     * @return string
     */
    public function render(): string
    {
        $content = $this->content();

        if ($this->parse_mode == ParseMode::markdown) {
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
            ->replace('%content%', $content . $this->renderReplyMarkup())
            ->toString();
    }

    protected function renderReplyMarkup(): string
    {
        $this->parameters();

        $reply_markup = $this->reply_markup;
        $inline_keyboard = $reply_markup['inline_keyboard'] ?? null;

        if ($inline_keyboard) {
            $rows = [];
            foreach ($inline_keyboard as $row) {
                $cells = [];
                foreach ($row as $cell) {
                    $text = $cell['text'] ?? null;
                    $url = $cell['url'] ?? null;
                    if ($text && $url) {
                        $cells[] = '<td><a href="' . $url . '" target="_blank">' . $text . '</a></td>';
                    } else {
                        $cells[] = '<td></td>';
                    }
                }
                $rows[] = '<tr>' . implode("\n", $cells) . '</tr>';
            }
            $inline_keyboard = '<table class="inline_keyboard">' . implode("\n", $rows) . '</table>';
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
