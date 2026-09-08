<?php

namespace Tests;

use Codewiser\Notifications\Listeners\NotificationMentions;
use Codewiser\Notifications\Messages\DatabaseMessage;
use Codewiser\Notifications\Models\DatabaseNotification;
use Codewiser\Notifications\Models\NotificationMention;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Notifications\Events\NotificationSent;
use PHPUnit\Framework\TestCase;
use Stubs\Post;
use Stubs\Tag;

class NotificationMentionsTest extends TestCase
{
    protected function setUp(): void
    {
        $capsule = new Capsule;

        $capsule->addConnection([
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        $capsule->setAsGlobal();
        $capsule->bootEloquent();

        $schema = $capsule->schema();

        $schema->create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable', 'notifications_notifiable_index');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        $schema->create('notification_mention', function (Blueprint $table) {
            $table->uuid('notification_id');
            $table->morphs('mentionable');

            // A custom pivot column added by the application's migration.
            $table->string('pivot_value')->nullable();

            $table->timestamps();

            $table->unique(['notification_id', 'mentionable_type', 'mentionable_id']);
        });

        $schema->create('posts', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->softDeletes();
        });

        $schema->create('tags', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
        });
    }

    /**
     * Send a message with the given attaches through the `database` channel.
     */
    private function makeEvent(DatabaseMessage $message, string $channel = 'database'): NotificationSent
    {
        $notification = new DatabaseNotification;
        $notification->id = (string) \Illuminate\Support\Str::uuid();
        $notification->type = 'TestNotification';
        $notification->notifiable_type = 'App\\Models\\User';
        $notification->notifiable_id = 1;
        $notification->data = $message->toArray();
        $notification->save();

        return new NotificationSent(new AnonymousNotifiable, $notification, $channel);
    }

    public function testHandleCreatesMentionRowsWithPivotValues()
    {
        $postA = new Post;
        $postA->save();

        $postB = new Post;
        $postB->save();

        $message = new DatabaseMessage;
        $message->attach($postA)->attach($postB, ['pivot_value' => 'answered']);

        $event = $this->makeEvent($message);

        $this->assertSame(0, NotificationMention::query()->count());

        (new NotificationMentions)->handle($event);

        $rows = NotificationMention::query()->get();

        $this->assertCount(2, $rows);
        $this->assertEqualsCanonicalizing(
            [$postA->getKey(), $postB->getKey()],
            $rows->pluck('mentionable_id')->map(fn($id) => (int) $id)->all()
        );
        $this->assertEqualsCanonicalizing(
            [1, 1],
            $rows->pluck('notification_id')->map(fn($id) => $id == $event->notification->id ? 1 : 0)->all()
        );

        $this->assertEquals('answered', $rows->firstWhere('mentionable_id', $postB->getKey())->pivot_value);
        $this->assertNull($rows->firstWhere('mentionable_id', $postA->getKey())->pivot_value);
    }

    public function testMultipleAttachesDoNotInterfereWithEachOther()
    {
        $posts = collect();

        foreach (range(1, 3) as $index) {
            $post = new Post;
            $post->save();
            $posts->push($post);
        }

        $message = new DatabaseMessage;
        $posts->each(fn(Post $post) => $message->attach($post));
        $message->attach($posts[1], ['pivot_value' => 'flagged']);

        (new NotificationMentions)->handle($this->makeEvent($message));

        $rows = NotificationMention::query()->get();

        // Each attached post holds its own row, none are mixed up or overwritten.
        $this->assertCount(3, $rows);
        $this->assertEqualsCanonicalizing(
            $posts->map(fn(Post $post) => $post->getKey())->all(),
            $rows->pluck('mentionable_id')->map(fn($id) => (int) $id)->all()
        );
        $this->assertEquals(
            'flagged',
            $rows->firstWhere('mentionable_id', $posts[1]->getKey())->pivot_value
        );
        $this->assertNull($rows->firstWhere('mentionable_id', $posts[0]->getKey())->pivot_value);
        $this->assertNull($rows->firstWhere('mentionable_id', $posts[2]->getKey())->pivot_value);
    }

    public function testHandleSkipsModelsNotImplementingMentionable()
    {
        $post = new Post;
        $post->save();

        $tag = new Tag;
        $tag->save();

        $message = new DatabaseMessage;
        $message->attach($post)->attach($tag);

        (new NotificationMentions)->handle($this->makeEvent($message));

        $this->assertSame(1, NotificationMention::query()->count());
        $this->assertEquals($post->getKey(), (int) NotificationMention::query()->first()->mentionable_id);
    }

    public function testHandleIgnoresNonDatabaseChannel()
    {
        $post = new Post;
        $post->save();

        $message = new DatabaseMessage;
        $message->attach($post);

        (new NotificationMentions)->handle($this->makeEvent($message, 'mail'));

        $this->assertSame(0, NotificationMention::query()->count());
    }
}