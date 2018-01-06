<?php


namespace BaseTree\Testing\Traits;


use Illuminate\Contracts\Console\Kernel;
use BaseTree\Testing\DatabaseTestCase;

trait DatabaseMigrations
{
    public function runDatabaseMigrations()
    {
        $this->artisan('migrate', ['--seed' => true]);
        $this->app[Kernel::class]->setArtisan(null);
        $this->beforeApplicationDestroyed(function () {
            $this->artisan('migrate:rollback');
        });
    }

    protected function setUpTraits()
    {
        $uses = array_flip(class_uses_recursive(DatabaseTestCase::class));
        if (isset($uses[DatabaseMigrations::class])) {
            $this->runDatabaseMigrations();
        }

        parent::setUpTraits();
    }
}