# Laravel Notifications

Provides Laravel Notification helpers.

It supports a few types of notification messages:
`mail`, `broadcast` and `database`.
All of them implement one contract, so we can build all these messages as one.

`broadcast` and `database` messages share a unified payload format:
[Web Notification](https://developer.mozilla.org/en-US/docs/Web/API/Notification).
This format is ready to implement on the frontend.

## Migrations

Change the `notifications.data` column to `json` type and create the 
`notification_mention` table.

```shell
php artisan vendor:publish --provider="Codewiser\Notifications\NotificationsServiceProvider"
 
php artisan migrate
```

## Message Contract

All messages — `mail`, `broadcast` and `database` — implement
`MessageContract`, so we can build all messages as one.

```php
use Codewiser\Notifications\Contracts\MessageContract;
use Codewiser\Notifications\Messages\MailMessage;
use Codewiser\Notifications\Messages\BroadcastMessage;
use Codewiser\Notifications\Messages\DatabaseMessage;
use Codewiser\Notifications\MarkdownTable;

class ReviewArticle extends \Illuminate\Notifications\Notification
{
    protected function build(MessageContract $message)
    {
        $message
            ->subject('Article Review')
            ->line('You need to review the article.')
            ->action('Review', url('/article', [
                'article' => $this->article->getKey()
            ]))
            // Format as blockquote
            ->quotation('Silence is golden');
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
                ->render()
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

`broadcast` message has its payload in
[Web Notification](https://developer.mozilla.org/en-US/docs/Web/API/Notification)
format.

## Database Message

`database` message (as a `broadcast`) has its
[Web Notification](https://developer.mozilla.org/en-US/docs/Web/API/Notification)
payload.

> N.B.  
> This package provides an extended `DatabaseNotification` class.
> Be sure to override the `User::notifications()` method.

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

Custom `NotificationBuilder` allows you to order notifications by priority, 
scope the query by notifiable, by notification class, or by mentioned objects 
(see below).

### Mentions

A mention is a relation between a database notification and one or more models. 

Let's say our app has a notification about a new post comment.

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
            ->attach($this->comment)
            ->attach($this->comment->post);
    }
}
```

If we attach post and comment models to a database notification, we may show a 
counter with unread notifications about this post to a user viewing the post. 
We may build a menu with an unread notification counter, etc.

```php
// User's unread notifications attached to any post:
$request->user()->notifications()
    ->whereMentioned(\App\Models\Post::class)
    ->whereUnread()
    ->count();

// User's unread notifications attached to the exact post and to any comments:
$request->user()->notifications()
    ->whereMentioned([
        $post, 
        \App\Models\Comment::class
    ])
    ->whereUnread()
    ->count();
```

The arguments of the `whereMentioned` method may be constrained with a callback:

```php
$request->user()->notifications()
    ->whereMentioned([
        $post, 
        \App\Models\Comment::class => fn($builder) => $builder
            ->wherePast('published_at')
    ]);
```

In this example we will get only notifications that are attached to the exact post 
and to comments whose `published_at` is in the past.

#### Mentions pivot values

Extend the `notification_mention` table with your own columns, then fill
them with the second argument of the `attach` method:

```php
return (new DatabaseMessage)
    ->subject('New comment')
    ->attach($this->comment)
    ->attach($this->comment->post, ['relevant' => true]);
```

Scope user notifications by those pivot values with a callback in `whereMentioned`.

```php
// User's unread notifications attached to any post that is flagged as relevant:
$request->user()->notifications()
    ->whereMentioned([
        \App\Models\Post::class => fn($query) => $query
            ->where('notification_mention.relevant', true),
    ])
    ->whereUnread()
    ->count();
```


### Persistent database notifications

Database notifications may be marked as persistent. 
Your application may prevent the user from marking such a notification as read.
The application will then mark it as read automatically once the user reaches 
a goal.

For example, a notification invites the user to review some article. 
The notification stays unread until the user reviews the article.
Once the article is reviewed, the notification is not relevant anymore.

```php
use Codewiser\Notifications\Messages\DatabaseMessage;
use Codewiser\Notifications\Models\DatabaseNotification;
use Codewiser\Notifications\Builders\NotificationBuilder;

// Send a persistent notification with the article attached.
class ReviewArticleNotification extends \Illuminate\Notifications\Notification
{
    public function toDatabase(): DatabaseMessage
    {
        return (new DatabaseMessage)
            ->subject('Review article')
            ->action('Review', route('article.show', $this->article))
            ->persistent('You must review the article')
            ->attach($this->article);
    }
}

// Get unread notifications about an article
$article->mentions()
    ->where(fn (NotificationBuilder $builder) => $builder
        ->whereNotifiable($user)
        ->whereUnread()
    );

// Later... mark the notification as read if the article was reviewed.
if ($article->wasReviewed()) {
    $user->notifications()
        ->whereType(ReviewArticleNotification::class)
        ->whereMentioned($article)
        ->markAsRead();
}
```

Add the `Mentionable` contract and `HasMentions` trait to every model 
that may be mentioned:

```php
use Codewiser\Notifications\Contracts\Mentionable;
use Codewiser\Notifications\Traits\HasMentions;
use Illuminate\Database\Eloquent\Model;

class Article extends Model implements Mentionable
{
    // Provides mentions relation
    use HasMentions;
}
```

## Previewing notifications

You may preview not only
[Mail](https://laravel.com/docs/10.x/notifications#previewing-mail-notifications),
but Broadcast Notifications too — the same way.