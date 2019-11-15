<?php

namespace BaseTree\Tests\Unit;


use Illuminate\Config\Repository as ConfigRepository;
use Illuminate\Http\Request;
use Tests\TestCase as LaravelTestCase;

abstract class TestCase extends LaravelTestCase
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var ConfigRepository
     */
    protected $config;

    protected function setUp(): void
    {
        parent::setUp();
        $this->request = $this->app->make(Request::class);
        $this->config = $this->app->make(ConfigRepository::class);
    }
}
