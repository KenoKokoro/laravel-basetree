<?php


namespace BaseTree\Tests\Unit\Responses;


use BaseTree\Providers\BaseTreeServiceProvider;
use BaseTree\Responses\HttpResponse;
use BaseTree\Tests\Unit\TestCase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\MessageBag;

class HttpResponseTest extends TestCase
{
    /**
     * @var HttpResponse
     */
    protected $instance;

    public function setUp(): void
    {
        parent::setUp();
        $this->instance = new HttpResponse;
    }

    /** @test */
    public function success_should_return_redirect_with_successful_message_and_additional_values(): void
    {
        $response = $this->instance->success('/home', '', ['append' => 'message']);

        $this->assertEquals('http://localhost/home', $response->getTargetUrl());
        $session = $response->getSession()->all();
        $this->assertEquals('Successfully executed.', $session['message']);
        $this->assertEquals('message', $session['append']);
    }

    /** @test */
    public function danger_should_return_redirect_with_danger_message_including_warnings_and_input_data(): void
    {
        $response = $this->instance->danger('/home', '', ['append' => 'message'], ['input' => 'old'],
            new MessageBag(['error1' => 'error1']));

        $this->assertEquals('http://localhost/home', $response->getTargetUrl());
        $session = $response->getSession()->all();
        $this->assertEquals('Danger.', $session['message']);
        $this->assertEquals('message', $session['append']);
        $this->assertCount(1, $session['_old_input']);
        $this->assertEquals('old', $session['_old_input']['input']);
        /** @var MessageBag $errors */
        $errors = $session['errors'];
        $this->assertCount(1, $errors->all());
        $this->assertEquals('error1', $errors->first('error1'));
    }

    /** @test */
    public function redirect_without_specific_path_will_return_to_the_previous_path(): void
    {
        URL::shouldReceive('previous')->andReturn('http://localhost/previous');
        URL::shouldReceive('to')->with('http://localhost/previous', [], null)->andReturn('http://localhost/previous');
        URL::shouldReceive('getRequest')->andReturn($this->request);

        $response = $this->instance->success();

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals('http://localhost/previous', $response->getTargetUrl());
    }

    /** @test */
    public function http_response_facade_is_registered(): void
    {
        $this->app->register(BaseTreeServiceProvider::class);
        $this->assertInstanceOf(HttpResponse::class, $this->app->make('basetree.response.http'));
    }
}
