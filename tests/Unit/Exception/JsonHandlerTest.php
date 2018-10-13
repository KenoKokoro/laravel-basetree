<?php


namespace BaseTree\Tests\Unit\Exception;


use BaseTree\Exception\LaravelHandler;
use BaseTree\Tests\Fake\Unit\DummyModel;
use BaseTree\Tests\Unit\TestCase;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Validation\Factory as Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class JsonHandlerTest extends TestCase
{
    /**
     * @var LaravelHandler
     */
    private $instance;

    /**
     * @var Validator
     */
    private $validator;

    public function setUp()
    {
        parent::setUp();
        $this->instance = new LaravelHandler($this->app);
        $this->validator = $this->app->make(Validator::class);
        $this->request->headers->set('accept', ['application/json']);
    }

    /** @test */
    public function handle_validation_exception(): void
    {
        $validator = $this->validator->make([], ['fail' => 'required']);
        $exception = new ValidationException($validator);

        /** @var JsonResponse $response */
        $response = $this->instance->render($this->request, $exception);
        $this->assertInstanceOf(JsonResponse::class, $response);
        $array = json_decode($response->content(), true);

        $this->assertEquals('The fail field is required.', $array['validator']['fail'][0]);
        $this->assertEquals('The given data was invalid.', $array['message']);
    }

    /** @test */
    public function handle_http_not_found_exception(): void
    {
        $exception = new NotFoundHttpException;

        /** @var JsonResponse $response */
        $response = $this->instance->render($this->request, $exception);
        $this->assertInstanceOf(JsonResponse::class, $response);
        $array = json_decode($response->content(), true);

        $this->assertEquals('Route does not exist.', $array['message']);
    }

    /** @test */
    public function handle_method_not_allowed_exception(): void
    {
        $exception = new MethodNotAllowedHttpException(['get']);

        /** @var JsonResponse $response */
        $response = $this->instance->render($this->request, $exception);
        $this->assertInstanceOf(JsonResponse::class, $response);
        $array = json_decode($response->content(), true);

        $this->assertEquals('Method not allowed on this route.', $array['message']);
    }

    /**
     * @test
     * @dataProvider exceptions
     * @param Exception $exception
     */
    public function handle_exceptions(Exception $exception): void
    {
        /** @var JsonResponse $response */
        $response = $this->instance->render($this->request, $exception);
        $this->assertInstanceOf(JsonResponse::class, $response);
        $array = json_decode($response->content(), true);

        $this->assertEquals($exception->getMessage(), $array['message']);
    }

    public function exceptions()
    {
        return [
            [(new ModelNotFoundException)->setModel(get_class(new DummyModel))],
            [new AuthorizationException('This action is unauthorized.')],
            [new UnauthorizedHttpException('Basic', 'Invalid credentials.')],
            [new AuthenticationException],
            [new Exception('Something bad happen.')]
        ];
    }
}