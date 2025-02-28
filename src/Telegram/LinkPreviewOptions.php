<?php

namespace Codewiser\Notifications\Telegram;

use Illuminate\Contracts\Support\Arrayable;

/**
 * @deprecated
 */
class LinkPreviewOptions implements Arrayable
{
    /**
     * @param  bool|null  $is_disabled True, if the link preview is disabled
     * @param  string|null  $url URL to use for the link preview. If empty, then the first URL found in the message text will be used
     * @param  string|null  $prefer_small_media True, if the media in the link preview is supposed to be shrunk; ignored if the URL isn't explicitly specified or media size change isn't supported for the preview
     * @param  string|null  $prefer_large_media True, if the media in the link preview is supposed to be enlarged; ignored if the URL isn't explicitly specified or media size change isn't supported for the preview
     * @param  string|null  $show_above_text True, if the link preview must be shown above the message text; otherwise, the link preview will be shown below the message text
     */
    public function __construct(
        public ?bool $is_disabled = null,
        public ?string $url = null,
        public ?string $prefer_small_media = null,
        public ?string $prefer_large_media = null,
        public ?string $show_above_text = null,
    )
    {
        //
    }

    public function toArray(): array
    {
        return array_filter([
            'is_disabled' => $this->is_disabled,
            'url' => $this->url,
            'prefer_small_media' => $this->prefer_small_media,
            'prefer_large_media' => $this->prefer_large_media,
            'show_above_text' => $this->show_above_text,
        ]);
    }
}