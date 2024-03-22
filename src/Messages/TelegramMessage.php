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
    protected ?bool $disable_notification = null;
    protected ?bool $protect_content = null;
    protected array $link_preview_options = [];

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
        return $this->linkPreviewOptions(is_disabled: $disable_web_page_preview);
    }

    /**
     * Describes the options used for link preview generation.
     *
     * @param  bool|null  $is_disabled True, if the link preview is disabled
     * @param  string|null  $url URL to use for the link preview. If empty, then the first URL found in the message text will be used
     * @param  bool|null  $prefer_small_media True, if the media in the link preview is supposed to be shrunk
     * @param  bool|null  $prefer_large_media True, if the media in the link preview is supposed to be enlarged
     * @param  bool|null  $show_above_text True, if the link preview must be shown above the message text
     *
     * @return $this
     *
     * @see https://core.telegram.org/bots/api#linkpreviewoptions
     */
    public function linkPreviewOptions(
        bool $is_disabled = null,
        string $url = null,
        bool $prefer_small_media = null,
        bool $prefer_large_media = null,
        bool $show_above_text = null
    ): static
    {
        if (!is_null($is_disabled)) {
            $this->link_preview_options['is_disabled'] = $is_disabled;
        }

        if (!is_null($url)) {
            $this->link_preview_options['url'] = $url;
        }

        if (!is_null($prefer_small_media)) {
            $this->link_preview_options['prefer_small_media'] = $prefer_small_media;
        }

        if (!is_null($prefer_large_media)) {
            $this->link_preview_options['prefer_large_media'] = $prefer_large_media;
        }

        if (!is_null($show_above_text)) {
            $this->link_preview_options['show_above_text'] = $show_above_text;
        }

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

        $parameters = [
            'parse_mode' => $this->parse_mode->value,
        ];

        if ($this->reply_markup) {
            $parameters['reply_markup'] = json_encode($this->reply_markup, JSON_UNESCAPED_UNICODE);
        }

        if ($this->link_preview_options) {
            $parameters['link_preview_options'] = json_encode($this->link_preview_options, JSON_UNESCAPED_UNICODE);
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
