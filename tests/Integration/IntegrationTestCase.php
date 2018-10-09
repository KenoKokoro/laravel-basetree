<?php


namespace BaseTree\Tests\Integration;


use BaseTree\Exception\Handler;
use BaseTree\Testing\DatabaseTestCase;
use BaseTree\Tests\Fake\Integration\DatabaseSeeder;
use BaseTree\Tests\Fake\Integration\EloquentUser;
use BaseTree\Tests\Fake\Integration\RouteServiceProvider;
use BaseTree\Tests\Fake\Integration\UserRepository;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTablesServiceProvider;

class IntegrationTestCase extends DatabaseTestCase
{
    /**
     * @var Request
     */
    protected $request;

    protected function setUp()
    {
        parent::setUp();
        $this->request = $this->app->make(Request::class);
    }

    /**
     * Boot the application the way we need it
     * @return Application
     */
    public function createApplication()
    {
        /** @var Application $app */
        $app = require __DIR__ . '/../../vendor/laravel/laravel/bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();
        $app->make(Hasher::class)->setRounds(4);

        $app->singleton(ExceptionHandler::class, Handler::class);
        $app->register(RouteServiceProvider::class);
        $app->register(DataTablesServiceProvider::class);
        $app->bind(UserRepository::class, EloquentUser::class);
        $this->setDatabase($app);

        return $app;
    }

    /**
     * Overwrite the default seeder class name
     * @return string
     */
    protected function getSeederClassName(): string
    {
        return DatabaseSeeder::class;
    }

    /**
     * Set the testing connection for the application
     * @param Application $application
     */
    private function setDatabase(Application $application): void
    {
        /** @var ConfigRepository $config */
        $config = $application->make(ConfigRepository::class);
        $config->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ":memory:",
            'prefix' => '',
        ]);
    }
}