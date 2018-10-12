<?php


namespace BaseTree\Testing\Traits;


use BaseTree\Testing\LaravelDatabaseTestCase;
use Illuminate\Contracts\Console\Kernel;

trait DatabaseMigrations
{
    public function runDatabaseMigrations()
    {
        $this->artisan('migrate', ['--seed' => true]);
        $this->artisan('db:seed', ['--class' => $this->getSeederClassName()]);
//        $this->app[Kernel::class]->setArtisan(null);
        $this->beforeApplicationDestroyed(function() {
            $this->artisan('migrate:rollback');
        });
    }

    protected function setUpTraits()
    {
        $uses = array_flip(class_uses_recursive(LaravelDatabaseTestCase::class));
        if (isset($uses[DatabaseMigrations::class])) {
            $this->runDatabaseMigrations();
        }

        parent::setUpTraits();
    }

    protected function getSeederClassName(): string
    {
        return 'DatabaseSeeder';
    }
}