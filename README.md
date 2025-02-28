# Laravel Notifications

Provides Laravel Notification helpers.

It supports few types of notification messages:
`mail`, `broadcast` and `database`. 
All of it implements one contract, so we could build all these messages as one. 

`broadcast` and `database` messages got unified payload format:
[Web Notification](https://developer.mozilla.org/en-US/docs/Web/API/Notification).
This format is ready to implement on frontend.

## Migration

Migrate `notifications.data` column to `json` type.

```shell
php artisan notifications:json
 
php artisan migrate
```

## Message Contract

All messages — `mail`, `broadcast` and `database` implements
`MessageContract`, so we can build messages as one.

```php
use Codewiser\Notifications\Contracts\MessageContract;
use Codewiser\Notifications\Messages\MailMessage;
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
    
    public function toBroadcast(): BroadcastMessage
    {
        return (new BroadcastMessage)
            ->tap(fn($message) => $this->build($message))
            // Remove action button
            ->withoutAction()
            // Keep notification on screen until user closes it
            ->requireInteraction()
            // Icon to display on notification
            ->icon('https://example.com/icon.svg');
            // etc
    }
    
    public function toDatabase(): DatabaseMessage
    {
        return (new DatabaseMessage)
            ->tap(fn($message) => $this->build($message))
            // Use level to order database notifications
            ->level('danger')
            // Create notification as already read
            ->silent();
    }
    
    public function toArray(): array
    {
        return $this->toDatabase()->toArray();
    }
}
```

## Broadcast Message

`broadcast` message has payload in
[Web Notification](https://developer.mozilla.org/en-US/docs/Web/API/Notification)
format.

## Database Message

`database` message (as a `broadcast`) has
[Web Notification](https://developer.mozilla.org/en-US/docs/Web/API/Notification)
payload.

> N.B.  
> This package provides extended `DatabaseNotification` class.
> Be sure to override User::notifications() method.

```php
use Codewiser\Notifications\Builders\NotificationBuilder;
use Codewiser\Notifications\Models\DatabaseNotification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class User extends Model
{
    public function notifications(): MorphMany|NotificationBuilder
    {
        return $this->morphMany(DatabaseNotification::class, 'notifiable');
    }
}
```

Custom `NotificationBuilder` allows to order notifications by priority, 
scope query by notifiable, by notification class or by mentioned objects 
(see below).

### Persistent database notifications and mentions

`database` notification may be marked as persistent. 
Your application may restrict user tries to mark such notification as read. 
And mark notification as read then user reaches goals.

`database` notification may be binded to a Model, 
so you can find notifications where Model was mentioned.

For example, notification invites user to review some article. 
The notification kept as unread until user reviews the article.
Then article is reviewed, the notification is not relevant anymore.

```php
use Codewiser\Notifications\Messages\DatabaseMessage;
use Codewiser\Notifications\Models\DatabaseNotification;
use Codewiser\Notifications\Builders\NotificationBuilder;

// Send persistent notification with mentioned article.
class ReviewArticleNotification extends \Illuminate\Notifications\Notification
{
    public function toDatabase(): DatabaseMessage
    {
        return (new DatabaseMessage)
            ->subject('Review article')
            ->action('Review', route('article.show', $this->article))
            ->persistent('You must review the article')
            ->bindTo($this->article);
    }
}

// Get unread notifications about an article
$article->mentions()
    ->where(fn (NotificationBuilder $builder) => $builder
        ->whereNotifiable($user)
        ->whereUnread()
    );

// Later... mark notification as read if article was reviewed.
if ($article->wasReviewed()) {
    $user->notifications()
        ->whereType(ReviewArticleNotification::class)
        ->whereMentioned($article)
        ->markAsRead();
}
```

To enable binding create a migration for `mentions` table.

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
but Broadcast Notifications too — the same way.