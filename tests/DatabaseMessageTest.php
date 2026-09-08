<?php

namespace Tests;

use Codewiser\Notifications\Messages\DatabaseMessage;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use PHPUnit\Framework\TestCase;
use Stubs\Post;

class DatabaseMessageTest extends TestCase
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

        $capsule->schema()->create('posts', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function testMentionsReturnsModelsForMultipleKeys()
    {
        $postA = new Post;
        $postA->save();

        $postB = new Post;
        $postB->save();

        $message = new DatabaseMessage;
        $message->attach($postA)->attach($postB);

        $mentions = $message->mentions();

        $this->assertCount(2, $mentions);
        $this->assertContains($postA->id, $mentions->pluck('id'));
        $this->assertContains($postB->id, $mentions->pluck('id'));
        $this->assertInstanceOf(Post::class, $message->bindedTo());
        $this->assertEqualsCanonicalizing([$postA->getKey(), $postB->getKey()], $message->data['options']['data']['attached'][Post::class]);
    }

    public function testMentionsRespectsLegacyBindKey()
    {
        $post = new Post;
        $post->save();

        $message = new DatabaseMessage;
        $message->data['options']['data']['bind'][Post::class] = $post->getKey();

        $this->assertCount(1, $message->mentions());
        $this->assertEquals($post->id, $message->bindedTo()?->id);
    }

    public function testAttachStoresPivotValues()
    {
        $post = new Post;
        $post->save();

        $message = new DatabaseMessage;
        $message->attach($post, ['mention_note' => 'hello']);

        $this->assertEquals(
            ['mention_note' => 'hello'],
            $message->data['options']['data']['pivot_values'][Post::class][$post->getKey()]
        );
        $this->assertContains($post->getKey(), $message->data['options']['data']['attached'][Post::class]);
        $this->assertContains($post->getKey(), $message->data['options']['data']['bind'][Post::class]);
    }

    public function testAttachKeepsStoredIdsUnique()
    {
        $post = new Post;
        $post->save();

        $message = new DatabaseMessage;
        $message->attach($post)->attach($post)->attach($post);

        $this->assertEquals([$post->getKey()], $message->data['options']['data']['attached'][Post::class]);
        $this->assertEquals([$post->getKey()], $message->data['options']['data']['bind'][Post::class]);
    }

    public function testAttachWithoutAttributesResetsStoredPivotValues()
    {
        $post = new Post;
        $post->save();

        $first = new DatabaseMessage;
        $first->attach($post, ['pivot_value' => 'a']);
        $first->attach($post);

        $this->assertArrayNotHasKey(
            'pivot_values',
            $first->data['options']['data'] ?? []
        );

        $replaced = new DatabaseMessage;
        $replaced->attach($post, ['pivot_value' => 'a']);
        $replaced->attach($post, ['pivot_value' => 'b']);

        $this->assertEquals(
            ['pivot_value' => 'b'],
            $replaced->data['options']['data']['pivot_values'][Post::class][$post->getKey()]
        );
    }

    public function testMentionsMergesLegacyBindAndAttachedIds()
    {
        $legacy = new Post;
        $legacy->save();

        $fresh = new Post;
        $fresh->save();

        $message = new DatabaseMessage;
        $message->data['options']['data']['bind'][Post::class] = $legacy->getKey();
        $message->data['options']['data']['attached'][Post::class] = [$fresh->getKey()];

        $mentions = $message->mentions();

        $this->assertCount(2, $mentions);
        $this->assertEqualsCanonicalizing([$legacy->getKey(), $fresh->getKey()], $mentions->pluck('id')->all());
    }
}