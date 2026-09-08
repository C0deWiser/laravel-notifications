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
    }

    public function testMentionsHandlesSingleKeyBackwardsCompatibility()
    {
        $post = new Post;
        $post->save();

        $message = new DatabaseMessage;
        $message->data['options']['data']['bind'][Post::class] = $post->getKey();

        $this->assertCount(1, $message->mentions());
        $this->assertEquals($post->id, $message->bindedTo()?->id);
    }
}