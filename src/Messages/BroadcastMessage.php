<?php

namespace Codewiser\Notifications\Messages;

use Codewiser\Notifications\Contracts\MessageContract;
use Codewiser\Notifications\Traits\AsWebNotification;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Support\Traits\Tappable;

/**
 * Format broadcast messages as Web notifications.
 */
class BroadcastMessage extends \Illuminate\Notifications\Messages\BroadcastMessage implements Renderable, MessageContract
{
    use Tappable, AsWebNotification;

    public function __construct(array $data = [])
    {
        parent::__construct($data);
    }

    /**
     * Render the message preview.
     */
    public function render(): string
    {
        $options = json_encode($this->data['options'] ?? [], JSON_UNESCAPED_UNICODE);

        // JSON-encode the subject so quotes and HTML in the title cannot break or inject the script.
        $title = json_encode((string) $this->subject, JSON_UNESCAPED_UNICODE);

        $js = <<<JS
(function () {
    'use strict';
    
    document.querySelector("button").addEventListener("click", notifyMe);
    
    function notifyMe() {
        if (!("Notification" in window)) {
            alert("This browser does not support desktop notification");
        } else if (Notification.permission === "denied") {
            alert("User denied desktop notification");
        } else if (Notification.permission === "granted") {
            const notification = new Notification($title, $options );
        } else if (Notification.permission !== "denied") {
            Notification.requestPermission().then((permission) => {
                if (permission === "granted") {
                    const notification = new Notification($title, $options );
                }
            });
        }
    }
})();
JS;


        return '<html lang="'.app()->getLocale().'">
<head></head>
<body>
<div style="width: 100%; height: 90vh; display: flex; justify-content: center; align-items: center;">
    <button style="font-size: 2rem">Notify me!</button>
</div>
<script type="application/javascript">'.$js.'</script>
</body>
</html>';
    }
}
