# Laravel Notifications

Provides Laravel Notification helpers.

## Migration

Migrate `notifications.data` column to `json` type.

```shell
php artisan notifications:json
 
php artisan migrate
```

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
            ]))
            // Format as blockquote
            ->quotation('Silent is gold');
    }
    
    public function toMail(): MailMessage
    {
        return (new MailMessage)
            ->tap(fn($message) => $this->build($message))
            // Markdown table
            ->table(fn(MarkdownTable $table) => $table
                ->row(['Title 1', 'Title 2'])
                ->row([':---', '---:'])
                ->row(['Text 1', 'Text 2'])
            );
    }
    
    public function toTelegram(): TelegramMessage
    {
        return (new TelegramMessage)
            ->tap(fn($message) => $this->build($message))
            // Do not render preview
            ->linkPreviewOptions(is_disabled: true)
            // Prevent message forwarding or saving
            ->protected()
            // Notification without a sound 
            ->silently();
    }
    
    public function toBroadcast(): BroadcastMessage
    {
        return (new BroadcastMessage)
            ->tap(fn($message) => $this->build($message))
            // Discard action button
            ->withoutAction();
    }
    
    public function toDatabase(): DatabaseMessage
    {
        return (new DatabaseMessage)
            ->tap(fn($message) => $this->build($message))
            // Create notification as read
            ->silent();
    }
    
    public function toArray(): array
    {
        return $this->toDatabase()->toArray();
    }
}
```

## Broadcast Message

Broadcast Message has payload in
[Web Notification](https://developer.mozilla.org/en-US/docs/Web/API/Notification)
format.

## Database Message

Database Message (as a Broadcast) has
[Web Notification](https://developer.mozilla.org/en-US/docs/Web/API/Notification)
payload.

### Persistent notification and Mentions

Database notification may be marked as persistent. 
User can not mark such notification as read. 
Notification will be marked as read then user reaches to goal.

Database notification may be binded to a Model, 
so you can find notifications where Model was mentioned.

For example, notification invites user to review some article. 
The notification kept as unread until user reviews the article.
Then article is reviewed, the notification is not relevant anymore.

```php
use Codewiser\Notifications\Messages\DatabaseMessage;
use Codewiser\Notifications\Models\DatabaseNotification;

// Notification
public function toDatabase(): DatabaseMessage
{
    return (new DatabaseMessage)
        ->subject('Review article')
        ->action('Review', route('article.show', $this->article))
        ->persistent('You must review the article')
        ->bindTo($this->article);
}

// Later...
if ($article->wasReviewd()) {
    $user->notifications()
        ->whereMentioned($article)
        ->markAsRead();
}
```

To enable this feature create a migration for `mentions` table.

```shell
php artisan notifications:mentions
 
php artisan migrate
```

Add `Mentioned` contract and `HasMentions` trait to every model, 
that may be mentioned:

```php
use \Codewiser\Notifications\Contracts\Mentioned;
use \Codewiser\Notifications\Traits\HasMentions;
use \Illuminate\Database\Eloquent\Model;

class Article extends Model implements Mentioned
{
    // Provides mentions relation
    use HasMentions;
}
```

## Previewing notifications

You may preview not only
[Mail](https://laravel.com/docs/10.x/notifications#previewing-mail-notifications),
but also Telegram and Broadcast Notifications — the same way.

## Enhanced DatabaseNotification Model

DatabaseNotification `data` attribute is
a [Web Notification](https://developer.mozilla.org/en-US/docs/Web/API/Notification)
object.

DatabaseNotification is Prunable.

Has improved phpdoc and custom Builder.