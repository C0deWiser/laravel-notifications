<?php

namespace Tests;

use Codewiser\Notifications\Models\DatabaseNotification;
use Codewiser\Notifications\Models\NotificationMention;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use PHPUnit\Framework\TestCase;
use Stubs\Post;

class NotificationBuilderTest extends TestCase
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
            $table->timestamps();

            $table->foreign('notification_id')
                ->references('id')
                ->on('notifications')
                ->cascadeOnDelete();
        });

        $schema->create('posts', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    private function makeNotification(?Post $post = null): DatabaseNotification
    {
        $notification = new DatabaseNotification;
        $notification->id = (string) \Illuminate\Support\Str::uuid();
        $notification->type = 'TestNotification';
        $notification->notifiable_type = 'App\\Models\\User';
        $notification->notifiable_id = 1;
        $notification->data = [];
        $notification->save();

        if ($post) {
            $mention = new NotificationMention;
            $mention->notification_id = $notification->id;
            $mention->mentionable_type = $post->getMorphClass();
            $mention->mentionable_id = $post->getKey();
            $mention->save();
        }

        return $notification;
    }

    public function testWhereMentionedByClassExcludesOrphanedMentions()
    {
        $existingPost = new Post;
        $existingPost->save();

        $orphanedPost = new Post;
        $orphanedPost->save();

        $withExisting = $this->makeNotification($existingPost);
        $withOrphan   = $this->makeNotification($orphanedPost);

        // The orphaned post is removed, but its mention row stays behind.
        $orphanedPost->forceDelete();

        $found = DatabaseNotification::query()
            ->whereMentioned(Post::class)
            ->pluck('id')
            ->all();

        $this->assertContains($withExisting->id, $found);
        $this->assertNotContains($withOrphan->id, $found);
    }

    public function testWhereMentionedByModelMatchesOnlyThatModel()
    {
        $postA = new Post;
        $postA->save();

        $postB = new Post;
        $postB->save();

        $withA = $this->makeNotification($postA);
        $this->makeNotification($postB);

        $found = DatabaseNotification::query()
            ->whereMentioned($postA)
            ->pluck('id')
            ->all();

        $this->assertEquals([$withA->id], $found);
    }

    public function testWhereMentionedByClassExcludesTrashedByDefaultButCanIncludeThem()
    {
        $trashedPost = new Post;
        $trashedPost->save();

        $withTrashed = $this->makeNotification($trashedPost);
        $trashedPost->delete();

        // By default a soft-deleted (trashed) mentionable is excluded.
        $withoutTrashed = DatabaseNotification::query()
            ->whereMentioned(Post::class)
            ->pluck('id')
            ->all();
        $this->assertNotContains($withTrashed->id, $withoutTrashed);

        // With a callback we can opt into including trashed mentionables.
        $withTrashedFound = DatabaseNotification::query()
            ->whereMentioned([Post::class => fn(Builder $builder) => $builder->withTrashed()])
            ->pluck('id')
            ->all();
        $this->assertContains($withTrashed->id, $withTrashedFound);
    }
}
