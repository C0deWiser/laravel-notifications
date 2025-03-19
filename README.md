# Laravel Notifications

Provides Laravel Notification helpers.

It supports few types of notification messages:
`mail`, `broadcast` and `database`. 
All of it implements one contract, so we could build all these messages as one. 

`broadcast` and `database` messages got unified payload format:
[Web Notification](https://developer.mozilla.org/en-US/docs/Web/API/Notification).
This format is ready to implement on frontend.

## Migrations

Change `notifications.data` column to `json` type and create 
`notification_mention` table.

```shell
php artisan vendor:publish
 
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

### Mentions

Mention is a relation between database notification and some model(s). 

Let's say our app has a notification about new post comment.

```php
use Codewiser\Notifications\Messages\DatabaseMessage;

class PostCommentNotification extends \Illuminate\Notifications\Notification
{
    public function __construct(public Comment $comment) {
        //
    }
    
    public function toDatabase($notifiable): DatabaseMessage
    {
        return (new DatabaseMessage)
            ->subject('New comment')
            ->bindTo($this->comment)
            ->bindTo($this->comment->post);
    }
}
```

If we bind post and comment models to a database notification, we may show a 
counter with unread notifications about this post to a user viewing a post. 
We may build a menu with unread notification counter, etc.

```php
// Unread notifications about any post:
$request->user()->notifications()
    ->whereMentioned(\App\Models\Post::class)
    ->whereUnread()
    ->count();

// Unread notifications about comments to exact post:
$request->user()->notifications()
    ->whereMentioned([
        $post, 
        \App\Models\Comment::class
    ])
    ->whereUnread()
    ->count();
```

Method `whereMentioned` arguments may be constrained with a callback:

```php
$user->notifications()
    ->whereMentioned([
        $post, 
        \App\Models\Comment::class => fn($builder) => $builder
            ->wherePast('published_at')
    ]);
```

In this example we will get only notifications that relates to exact post 
and to comments, that has `published_at` in the past.

### Persistent database notifications

Database notifications may be marked as persistent. 
Your application may restrict user tries to mark such notification as read.
Application will mark notification as read automatically, then user reaches 
goals.

For example, notification invites user to review some article. 
The notification stays unread until user reviews the article.
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