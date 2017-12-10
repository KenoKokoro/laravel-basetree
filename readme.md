## Installation
`composer install kenokokoro/laravel-basetree`

After the package is pulled in, register the Service Provider in your `AppServiceProvider`:
```php
    public function register() 
    {
        $this->app->register(BaseTree\Providers\BaseTreeServiceProvider::class);
    }
```

That should be it.

## Description
Basic structure which is basically meant for RESTful APIs. Includes base classes which by their 
extending should allow working RESTful API endpoint for single resource. It forces the code to be separated
in business logic layer and data access layer, which results in much cleaner code.

## Includes
1. Error handling just by extension of the `BaseTree\Exception\Handler.php`
2. Base controller with required methods `BaseTree\Controller\RestfulJsonController.php`
3. Base resource which is basically a class dedicated for the business logic 
on given Model `BaseTree\Resources\Base.php`
4. Generic separated classes for http or json response.
5. Basic Model which is used in every resource and data access layer `BaseTree\Models\Model.php`.
Created models should extend this model.
6. Data access layer which wraps the models in separate repository for better structure and reusability
of the already used database calls. Every repository have interface and implementation.
7. Each database update is wrapped inside transaction, so if you don't receive your controllers response,
and got an exception nothing will be presisted in the database. 
8. `DatabaseTestCase` which contains a lot of helpers for database testing.

## Usage
### Requirements
1. Created migration and Model for your resource. Note that the model should extend the BaseTree model.
So in your `app\Models` directory create `Foo.php`

```php
    namespace App\Models;
    
    use BaseTree\Models\Model;
    
    class Foo extends Model {
    
        protected $fillable = ['name', 'description'];
    }
```
You must set the `$fillable` attribue in order to works properly.

2. Create the data access layer for the created model. For example you can create a folder under your
app folder called `DAL` and just a folder with the model name, in this case `Foo`
So in `app/DAL/Foo` create `FooRepository.php`:
```php
    namespace App\DAL\Foo;

    use BaseTree\Eloquent\RepositoryInterface;

    interface FooRepository extends RepositoryInterface {
    }
```

Now the implementation that is implementing the newly created interface.
In `app/DAL/Foo` create `EloquentFoo.php`
```php
    namespace App\DAL\Foo;

    use BaseTree\Eloquent\BaseEloquent;

    class EloquentFoo extends BaseEloquent implements FooRepository {
    }
```
Now bind everything in your custom created service provider. I would do:
In `app/DAL` create `DalServiceProvider.php`
```php
    namespace App\DAL;

    use Illuminate\Support\ServiceProvider;
    use App\DAL\Foo\FooRepository;
    use App\DAL\Foo\EloquentFoo;
    
    class DalServiceProvider extends ServiceProvider {
        public function register() {
            $bindings = [
                FooRepository::class => EloquentFoo::class,
                # Every other repository must be registered here
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

3. Create the dedicated resource responsible for the bussines logic rules and interaction with the 
data access layer. Create a folder inside your `app` folder called `Resources` or `BLL`, and in there
you can keep you model resources.
In `app/Resources` create the file `FooResource.php`:
```php
    namespace App\Resources;
    
    use BaseTree\Resources\Base;
    use App\DAL\FooRepository;
    
    class FooResource extends Base {
    
        public function __construct(FooRepository $repository) {
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

The `BaseTree\Resources\Contracts\ResourceCallbacks` interface contains the `created()` and `updated()`
methods which are basically hooks after resource is created or updated. Passed `$dependencyAttributes` values
contains everything except the `$fillable` attributes that are set on your model. This is good since 
using this values you can easaly update your relations. 

The `$attributes` contains the `$fillable` attributes.

4. Now the controller. Each controller should extend the `BaseTree\Controller\RestfulJsonController`.
In `app/Http/Controllers` (or inside somewhere else) create your `FoosController.php`.
```php
    namespace App\Http\Controllers;
    
    use BaseTree\Controller\RestfulJsonController;
    use App\Resources\FooResource;
    
    class FoosController extends RestfulJsonController {
        
        public function __construct(FooResource $resource) {
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
query strings using the php function `http_build_query(['constraints' => ['name|=|bar']])`. 
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
In `tests/Models` create `FooTest.php` which coresponds to your `Foo.php` Model. Let's asume that your
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
If you need additional functionalities you can always override the parent methods. For example, if you want
to generate slug for you resource which only has name as value, in your `app\Resources\FooResource.php`
```php
    namespace App\Resources;
    
    use BaseTree\Resources\Base;
    use App\DAL\FooRepository;
    
    class FooResource extends Base 
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


## TODO:
1. Tests for everything
2. Artisan generator

## License
The BaseTree package is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT)

