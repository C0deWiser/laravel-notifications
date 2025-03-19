<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('notification_mention', function (Blueprint $table) {
            $table->uuid('notification_id');
            $table->morphs('mentionable');

            $table->foreign('notification_id')
                ->on('notifications')
                ->references('id')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->unique(['notification_id', 'mentionable_type', 'mentionable_id'], 'notification_mention_notification_id_mentionable_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::drop('notification_mention');
    }
};
