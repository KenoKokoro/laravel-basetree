<?php


namespace BaseTree\Tests\Integration;


use BaseTree\Exception\Handler;
use BaseTree\Testing\LumenDatabaseTestCase;
use BaseTree\Tests\Fake\Integration\DatabaseSeeder;
use BaseTree\Tests\Fake\Integration\EloquentUser;
use BaseTree\Tests\Fake\Integration\Lumen\ConsoleKernel;
use BaseTree\Tests\Fake\Integration\UserRepository;
use Dotenv\Dotenv;
use Dotenv\Exception\InvalidPathException;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Laravel\Lumen\Application;

class LumenTestCase extends LumenDatabaseTestCase
{
    /**
     * @var Request
     */
    protected $request;

    public function setUp()
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
        $app = $this->buildApp();

        $app->singleton(ExceptionHandler::class, Handler::class);

        $app->instance('path.config', app()->basePath() . DIRECTORY_SEPARATOR . 'config');
        $app->instance('path.storage', app()->basePath() . DIRECTORY_SEPARATOR . 'storage');
        $app->bind(UserRepository::class, EloquentUser::class);
        $this->setDatabase($app);
        $this->setRoutes($app);

        return $app;
    }

    /**
     * Overwrite the default seeder class name
     * @return string
     */
    protected function getSeederClassName(): string
    {
        DatabaseSeeder::$isLumen = true;

        return DatabaseSeeder::class;
    }

    /**
     * Mock the app.php from Lumen's bootstrap file
     * @return Application
     */
    private function buildApp(): Application
    {
        try {
            (new Dotenv(__DIR__ . '/../'))->load();
        } catch (InvalidPathException $e) {
            //
        }

        $app = new Application(realpath(__DIR__ . '/../../vendor/laravel/lumen/'));
        $app->singleton(ExceptionHandler::class, Handler::class);
        $app->singleton(Kernel::class, ConsoleKernel::class);
        $app->withFacades(false, []);

        return $app;
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

        Schema::create('users', function(Blueprint $table) {
            $table->increments('id');

            $table->string('name');
            $table->string('email');

            $table->timestamps();
        });
    }

    private function setRoutes(Application $application): void
    {
        $application->router->get('get-route-no-action', 'MissingController@index');
    }
}