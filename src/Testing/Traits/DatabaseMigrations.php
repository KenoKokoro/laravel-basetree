<?php


namespace BaseTree\Testing\Traits;


use BaseTree\Testing\LaravelDatabaseTestCase;

trait DatabaseMigrations
{
    public function runDatabaseMigrations(): void
    {
        $this->artisan('migrate', ['--seed' => true]);
        $this->artisan('db:seed', ['--class' => $this->getSeederClassName()]);
        $this->beforeApplicationDestroyed(function() {
            $this->artisan('migrate:rollback');
        });
    }

    protected function setUpTraits(): void
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