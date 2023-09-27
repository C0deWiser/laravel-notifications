# Laravel Notifications

Provides Laravel Notification helpers.

## Migration

Migrate `notifications.data` column to `json` type.

```shell
php artisan notifications:json
 
php artisan migrate
```

## Broadcast Message

Broadcast Message has payload in 
[Web Notification](https://developer.mozilla.org/en-US/docs/Web/API/Notification) 
format.

## Database Message

Database Message as a Broadcast has 
[Web Notification](https://developer.mozilla.org/en-US/docs/Web/API/Notification) 
payload.

Database message may be binded to a Model, so you can find and mark this
message as read.

For example, notification invites user to review some article. Then article
is reviewed, the notification is not relevant anymore.

```php
use Codewiser\Notifications\Messages\DatabaseMessage;
use Codewiser\Notifications\Models\DatabaseNotification;

// Notification
public function toDatabase(): DatabaseMessage
{
    return (new DatabaseMessage)
        ->subject('Review article')
        ->bindTo($this->article);
}

public function toArray(): array
{
    return $this->toDatabase()->toArray();
}

// Later...
if ($article->wasReviewd()) {
    DatabaseNotification::query()
        ->whereBindedTo($article)
        ->markAsRead();
}
```

This fires `NotificationWasRead` event, that can be broadcasted to a frontend.

## Message Contract

All Messages — Mail, Telegram, Broadcast and Database implements 
`MessageContract`, so we can build messages as one.

```php
use Codewiser\Notifications\Contracts\MessageContract;
use Codewiser\Notifications\Messages\MailMessage;
use Codewiser\Notifications\Messages\TelegramMessage;
use Codewiser\Notifications\Messages\BroadcastMessage;
use Codewiser\Notifications\Messages\DatabaseMessage;

class ReviewArticle extends \Illuminate\Notifications\Notification
{
    protected function build(MessageContract $message)
    {
        $message
            ->subject('Article Review')
            ->line('You need to review article.')
            ->action('Review', url('/article', [
                'article' => $this->article->getKey()
            ]));
    }
    
    public function toMail(): MailMessage
    {
        return (new MailMessage)
            ->tap(fn($message) => $this->build($message));
    }
    
    public function toTelegram(): TelegramMessage
    {
        return (new TelegramMessage)
            ->tap(fn($message) => $this->build($message))
            ->withoutPreview();
    }
    
    public function toBroadcast(): BroadcastMessage
    {
        return (new BroadcastMessage)
            ->tap(fn($message) => $this->build($message))
            ->withoutAction();
    }
    
    public function toDatabase(): DatabaseMessage
    {
        return (new DatabaseMessage)
            ->tap(fn($message) => $this->build($message))
            ->silent()
            ->bindTo($this->article);
    }
    
    public function toArray(): array
    {
        return $this->toDatabase()->toArray();
    }
}
```

## Preview

You may preview not only 
[Mail](https://laravel.com/docs/10.x/notifications#previewing-mail-notifications), 
but also Telegram and Broadcast Notifications — the same way.

## Enhanced DatabaseNotification Model

DatabaseNotification `data` attribute is a `DatabaseMessage` object.

DatabaseNotification is Prunable.

Has improved phpdoc and custom Builder.