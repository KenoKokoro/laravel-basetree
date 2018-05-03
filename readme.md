## Installation
`composer require kenokokoro/laravel-basetree`

After the package is pulled in, register the Service Provider in your `AppServiceProvider`:
```php
    public function register() 
    {
        $this->app->register(BaseTree\Providers\BaseTreeServiceProvider::class);
    }
```

That should be it.

## Description
Basic multilayer structure which is basically meant for RESTful APIs and Web CRUD resources. Includes base classes which
 by their extending should allow working RESTful API endpoint and CRUD operations for single resource. It forces the code to be 
separated in business logic layer and data access layer, which results in much cleaner code.

## Includes
1. Error handling just by extension of the `BaseTree\Exception\Handler.php`
2. Base controllers with required methods 

    a.`BaseTree\Controller\RestfulJsonController.php` for RESTful APIs
    
    b.`BaseTree\Controller\WebController.php` for web based CRUD operations
    
3. Base resource which is basically a class dedicated for the business logic 
on given Model `BaseTree\Resources\BaseResource.php`
4. Generic separated classes for http or json response.
5. Basic model interface which is used in every resource and data access layer `BaseTree\Models\BaseTreeModel.php`.
Created models should implement this model.
6. Data access layer which wraps the models in separate repository for better structure and re-usability
of the already used database calls. Every repository have own interface and implementation.
7. Each database update is wrapped inside transaction, so if you don't receive your controllers response,
and got an exception nothing will be persisted in the database. 
8. `DatabaseTestCase` which contains a lot of helpers for integration testing using the PHPUnit framework.

## Usage
### Requirements
1. Created migration and Model for your resource. Note that the model should extend the BaseTree model.

    So in your `app\Models` directory create `Foo.php`

    ```php
        namespace App\Models;
        
        use Illuminate\Database\Eloquent\Model;
        use BaseTree\Models\BaseTreeModel;
        
        class Foo extends Model implements BaseTreeModel 
        {
            protected $fillable = ['name', 'description'];
        }
    ```
    You must set the `$fillable` attribute in order to works properly.

2. Create the data access layer for the created model. ([Automatic creation](#artisan-generators))

    For example you can create a folder under your
    `app` folder called `DAL` and just a folder with the model name, in this case `Foo`

    So in `app/DAL/Foo` create `FooRepository.php`:
    ```php
        namespace App\DAL\Foo;

        use BaseTree\Eloquent\RepositoryInterface;

        interface FooRepository extends RepositoryInterface 
        {
        }
    ```

    Now the repository implementation that is implementing the newly created interface.

    In `app/DAL/Foo` create `EloquentFoo.php`
    ```php
        namespace App\DAL\Foo;

        use BaseTree\Eloquent\BaseEloquent;
        use App\Models\Foo;

        class EloquentFoo extends BaseEloquent implements FooRepository 
        {
            public function __construct(Foo $model)
            {
                parent::__construct($model);
            }
        }
    ```
    Now bind everything in your custom created service provider. I would do:

    In `app/DAL` create `DalServiceProvider.php`
    ```php
        namespace App\DAL;

        use Illuminate\Support\ServiceProvider;
        use App\DAL\Foo\FooRepository;
        use App\DAL\Foo\EloquentFoo;
        
        class DalServiceProvider extends ServiceProvider 
        {
            public function register() {
                $bindings = [
                    FooRepository::class => EloquentFoo::class,
                    # Every other repository should be registered here
                ];
                
                foreach($bindings as $interface => $implementation) {
                    $this->app->bind($interface, $implementation);
                }
            }
        }
    ```

    And register the `DalServiceProvider` in your `AppServiceProvider`:
    ```php
        use App\DAL\DalServiceProvider;

        ...
        public function register() {
            $this->app->register(DalServiceProvider::class);
        }
    ```

    With this you are done with the Data Access Layer structure. You can inject you interfaces whenever 
    you need and reuse your queries.

3. Create the dedicated resource responsible for the business logic rules and interaction with the 
data access layer. Create a folder inside your `app` folder called `Resources` or `BLL`, and in there
you can keep you model resources. ([Automatic creation](#artisan-generators))

    In `app/BLL` create the file `FooResource.php`:
    ```php
        namespace App\BLL;
        
        use BaseTree\BLL\BaseResource
        use App\DAL\FooRepository;
        
        class FooResource extends BaseResource
        {
            public function __construct(FooRepository $repository) 
            {
                parent::__construct($repository);
            }
        }
    ```
    With this you are pretty much done for the resource, but this package includes some helper interfaces
    to help you structure your validations, creating and updating.

    The `BaseTree\Resources\Contracts\ResourceValidation` interface will force you to put the 
    `storeRules()`, `updateRules()` and `destroyRules()`. Having this interface implemented on your resource,
    your requests on `store()`, `update()` and `destroy()` would be validated. If you don't need any validation
    for certain method, just return empty array.
    You can also use them as separate depending on your needs (Check the `BaseTree\Resources\Contracts\ResourceValidation`,
    all the extended interfaces have their own method and can be used individually if needed)

    The `BaseTree\Resources\Contracts\ResourceCallbacks` interface contains the `created()` and `updated()`
    methods which are basically hooks after resource is created or updated. Passed `$dependencyAttributes` values
    contains everything except the `$fillable` attributes that are set on your model. This is good since 
    using this values you can easily update your relations. The callbacks also can be used individually if needed just like 
    the resource validations that are mentioned above (`BaseTree\Resources\Contracts\ResourceCallbacks` check the extending interfaces)

    The `$attributes` contains the `$fillable` attributes.

4. Now the controller. Each controller should extend the `BaseTree\Controllers\RestfulJsonController` or 
`BaseTree\Controllers\WebController` depending on your need. ([Automatic creation](#artisan-generators))

    In `app/Http/Controllers` (or inside somewhere else) create your `FoosController.php`.
    ```php
        namespace App\Http\Controllers;
        
        use BaseTree\Controllers\RestfulJsonController;
        use App\BLL\FooResource;
        
        class FoosController extends RestfulJsonController 
        {
            public function __construct(FooResource $resource) 
            {
                parent::__construct($resource);
            }
        }
    ```

5. Now you are ready for your route:
    ```php
        Route::resource('foos', 'FoosController')->except(['edit']);
    ```

### Request - Response
Note that you will always have to set your Accept header to `application/json`.

Having this being said, now you have RESTful API with just few classes creation:
1. Visiting `/api/foos&datatable=1` will return you response which can be used as ajax source
for jquery datatables plugin.
2. Visiting `/api/foos&paginate=1&perPage=10` will return you paginated response with next and previous
urls in the response. Default value for `perPage` is 15.
3. Visiting `api/foos&constraints[0]=name|=|bar` will return all foos with name bar. You can build 
query strings using the php function `http_build_query(['constraints' => ['name|=|bar', 'active|1']])`. 
4. Visiting `api/foos&fields[0]=Bar` will return all foos but with the `Bar` relation included in the
response.

The structure for the constraints value is `columnName|operator|value`. If you need to add constraint
by another column: columnName|operator|\`otherColumn\`

### Testing
This package contains database test class which can make your database required testing much easier.
1. Setup your DB_CONNECTION to testing inside `phpunit.xml`:
    ```xml
        <env name="DB_CONNECTION" value="testing"></env>
    ```
2. Setup your testing connection inside your `database.php` config file:
    In `config/database.php` under `connections` add:
    ```php
        'testing' => [
            'driver' => 'sqlite',
            'database' => ':memory:',
        ],
    ```
    This will give the ability for the `BaseTree\Tests\Traits\DatabaseMigrations` which is included inside
    `BaseTree\Tests\DatabaseTestCase` to migrate and seed before your test start, and rollback after your 
    test is finished. Like this you will always have fresh database.
#### Testing models
In `tests/Models` create `FooTest.php` which corresponds to your `Foo.php` Model. Let's asume that your
`Foo` model has one `Bar` model.
```php
    namespace Tests\Models;

    use App\Models\Foo;
    use App\Models\Bar;
    use BaseTree\Tests\DatabaseRequiredTestCase;

    class FooTest extends DatabaseRequiredTestCase
    {
        /** @test */
        public function a_foo_has_one_bar()
        {
            $foo = create(Foo::class);
            $bar = create(Bar::class);

            $foo->bar()->save($bar);

            $this->assertHasOne($foo, $bar, 'bar', ['id' => $bar->id, 'foo_id' => $foo->id]);
        }
    }
```

#### Testing controller endpoints
After you create your `FoosController` which extends the `DatabaseTestCase` you can:
```php
    use BaseTree\Responses\JsonResponse;
    use App\Models\Foo;

    ...
    
    /** @test */
    public function it_should_fetch_all_foos()
    {
        $response = $this->jsonGet(route("foos.index"));
        $response->assertStatus(JsonResponse::HTTP_OK)->assertJsonStructure(['status', 'message', 'data']);
    }
    
    /** @test */
    public function it_requires_data_in_order_to_store_foo()
    {
        $response = $this->jsonPost(route('foos.store'));
        $response->assertStatus(JsonResponse::HTTP_UNPROCESSABLE_ENTITY)->assertJsonStructure([
            'status',
            'message',
            'validator'
        ]);
        $validator = $response->json()['validator'];

        $this->assertCount(7, $validator);
        
        # Response messages assertions. Third argument is value that laravel will translate without the _
        $this->assertFieldRequired($validator, 'name');
        $this->assertEmailField($validator, 'user_email', 'user email');
        $this->assertPasswordIsConfirmed($validator, 'password');
        $this->assertValueIn($validator, 'value_from_enum', 'value from enum');
        $this->assertFieldExist($validator, 'id');
        $this->assertFieldIsArray($validator, 'array');
        $this->assertValueIsUnique($validator, 'unique_column', 'unique column');
    }
    
    /** @test */
    public function foo_can_be_stored()
    {
        $response = $this->jsonPost(route('foos.store', ['name' => 'Foo Name']));
        $response->assertStatus(JsonResponse::HTTP_UNPROCESSABLE_ENTITY)->assertJsonStructure([
            'status',
            'message',
            'validator'
        ]);
        
        $this->assertCreated(new Foo, ['name' => 'Foo Name']);
    }
```

### Feeling limited with just empty files
If you need additional functionality you can always override the parent methods. For example, if you want
to generate slug for you resource which only has name as value, in your `app\BLL\FooResource.php`
```php
    namespace App\BLL;
    
    use BaseTree\Resources\BaseResource;
    use App\DAL\FooRepository;
    
    class FooResource extends BaseResource
    {
        public function __construct(FooRepository $repository) 
        {
            parent::__construct($repository);
        }
        
        public function store(array $attributes) 
        {
            $attributes['slug'] = str_slug($attributes['name']);
            # Or whatever logic you need here
            
            return parent::store($attributes);
        }
        
    }
```
Same thing works for controllers and DAL. Whatever you need to be customized can be extended and overwritten.

## Artisan generators
1. Generate data access layer. This command will generate repository and eloquent implementation. Don't forget to bind this into your `ServiceProvider`
    ```php
    Usage:
      php artisan basetree:dal [options]
    
    Options:
          --model[=MODEL]                              Fully qualified model name including the namespace
          --interface-folder[=INTERFACE-FOLDER]        Folder where to create the DAL interface [default: "app/DAL/[model-name]"]
          --interface-namespace[=INTERFACE-NAMESPACE]  Namespace to create the DAL interface under [default: "App\DAL\[model-name]"]
          --dal-folder[=DAL-FOLDER]                    Folder where to create the DAL implementation [default: "app/DAL/[model-name]"]
          --dal-namespace[=DAL-NAMESPACE]              Namespace to create the DAL implementation under [default: "App\DAL\[model-name]"]
    ```
    Example: `php artisan basetree:dal --model=App\\Models\\User`
    
2. Generate business logic layer. This will generate a resource with the repository interface injected inside your contructor
    ```php
    Usage:
      php artisan basetree:bll [options]
    
    Options:
          --model[=MODEL]                  Fully qualified model name including namespace
          --dal-interface[=DAL-INTERFACE]  Fully qualified data access layer name including namespace
          --folder[=FOLDER]                Folder where to create the BLL [default: "app/BLL/"]
          --namespace[=NAMESPACE]          Namespace to create the BLL under [default: "App\BLL"]
    ```
    Example: `php artisan basetree:bll --model=App\\Models\\User --dal-interface=App\\DAL\\User\\UserRepository`
    
3. Generate controller. The generated controller will have the given business logic layer injected inside the constructor

NOTE: At this point, the generator is only creating controller extending the `RestfulJsonController`. You will have to change
the extension manually on the generated controller in order to extend the `WebController` or create it manually

    ```php
    Usage:
      php artisan basetree:controller [options]
    
    Options:
          --model-plural[=MODEL-PLURAL]  Plural form of the model name. For instance if the model is User, you should send here Users
          --bll[=BLL]                    Fully qualified business logic layer name including namespace
          --folder[=FOLDER]              Folder where to create the controller [default: "app/Http/Controllers/Api/"]
          --namespace[=NAMESPACE]        Namespace to create the controller under [default: "App\Http\Controllers\Api"]

    ```
    Example: `php artisan basetree:controller --model-plural=users -bll=App\\BLL\UserResource`

4. Publish the docker-compose architecture. Check out the .env.docker-compose.example
for the required variables to make the docker containers just work.
    ```php
    Usage:
      php artisan basetree:boilerplates [options]
    
    Options:
          --docker-compose  Publish the docker structure
 
    Help:
      Publish some already predefined environments.
          --docker-compose: Docker environment for local development (nginx 1.13, php7.1-fpm + composer, npm 3.3, nodejs 6.7, MariaDB 10.3, phpmyadmin 4.7)

    ```
    Example: `php artisan basetree:boilerplates --docker-compose`
    
    Required variables:
    
    - `DOCKER_HOST_UID=1000` # Your host user id. Check it by doing `echo $UID` or `id username`.
    - `DOCKER_HOST_GID=1000` # Your host group id. Check it by doing `echo $GID` or `id username`.
    - `DATABASE_LOCAL_STORAGE=/opt/mariadb/project` # Where to keep your database files on your host machine. This is 
    required since if you run `docker-compose down -v` this will destroy the data in your database, if the storage is not
    mounted on your host machine.
    - `PMA_PORT=81` # Public exposed port for PhpMyAdmin. If running on QA environment or if you don't need you can remove 
    if from the `docker-compose.yml` file, or by running `docker-compose stop phpmyadmin`
    - `NGINX_SERVER_NAME=localhost` # The virtual host name 
    - `NGINX_PORT=80` # Public exposed port for the nginx container. Should be `80` in order to avoid adding your port 
    after your domain. Example: If your `NGINX_PORT=8080`, you will have to access it: `localhost:8080` on your browser. 
    Also if you already have some services listening to the given port, you will have to shut them down.
    - `CONTAINER_ROOT=/application` # The name of your project inside the container.
    - `DB_ROOT_PASSWORD=root-pass123` # MariaDB root password
    - `QA_HTTP_HOST=` # If you are running multiple docker instances, and you want to bind them all to `80` port, you will have
    to specify the `fastcgi_param HTTP_HOST` here, in order your application to redirect to your proxy url.
    - `DB_HOST=mariadb` # If you are using the docker mariadb instance, instead of your own already installed.
    
    After all this is set up, you will have to run `docker-compose up -d` and wait for the build to finish. Check your containers
    status by running `docker-compose ps`. You will have to see something like this:
    ```php
            Name               Command                          State               Ports                
    ---------------------------------------------------------------------------------------------------------------------------------------------------------------------
    tutorial_app_1         docker-php-entrypoint /sta ...       Up           443/tcp, 0.0.0.0:80->80/tcp, 9000/tcp
    tutorial_mariadb_1     docker-entrypoint.sh mysqld          Up           0.0.0.0:3307->3306/tcp               
    tutorial_phpmyadmin_1  /run.sh phpmyadmin                   Up           0.0.0.0:81->80/tcp 
    ```

## TODO:
1. ~~Tests for everything~~
2. ~~Artisan generator~~
3. Wiki examples and explanations
4. Include JWT support
5. Add artisan endpoint generator

## License
The BaseTree package is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT)

