<?php

namespace Codewiser\Notifications\Console;

use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Composer;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'notifications:json')]
class NotificationJsonCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'notifications:json';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a migration for modifying notifications table';

    /**
     * Create a new notifications table command instance.
     *
     * @param Filesystem $files
     * @return void
     */
    public function __construct(protected Filesystem $files)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return void
     * @throws FileNotFoundException
     */
    public function handle(): void
    {
        $fullPath = $this->createBaseMigration();

        $this->files->put($fullPath, $this->files->get(__DIR__.'/stubs/notifications.stub'));

        $this->components->info('Migration created successfully.');
    }

    /**
     * Create a base migration file for the notifications.
     */
    protected function createBaseMigration(): string
    {
        $name = 'json_notifications_table';

        $path = $this->laravel->databasePath().'/migrations';

        return $this->laravel['migration.creator']->create($name, $path);
    }
}
