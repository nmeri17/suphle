<?php

namespace Suphle\Tests\Unit\Routing;

use Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators\{User5CanaryController, FooCanaryController, DefaultCanaryController};
use Suphle\Tests\Unit\TestRunner;
use Suphle\Contracts\Auth\AuthStorage;
use Suphle\Request\PayloadStorage;

class CanaryControllerTest extends TestRunner
{
    public function test_user5_canary_controller_with_user5()
    {
        // Given user ID is 5
        $this->mock(AuthStorage::class, function ($mock) {
            $mock->shouldReceive('getId')->andReturn(5);
        });

        $controller = $this->container->getClass(User5CanaryController::class);
        
        // When accessing user5-profile
        $response = $controller->user5Handler();
        
        // Then should return USER5 profile
        $this->assertEquals(['profile' => 'USER5 user profile!'], $response->getResponseData());
    }

    public function test_user5_canary_controller_with_other_user()
    {
        // Given user ID is not 5
        $this->mock(AuthStorage::class, function ($mock) {
            $mock->shouldReceive('getId')->andReturn(1);
        });

        $controller = $this->container->getClass(User5CanaryController::class);
        
        // When accessing user5-profile
        $response = $controller->user5Handler();
        
        // Then should return STABLE profile
        $this->assertEquals(['profile' => 'STABLE user profile.'], $response->getResponseData());
    }

    public function test_foo_canary_controller_with_foo_in_payload()
    {
        // Given payload has 'foo' key
        $this->mock(PayloadStorage::class, function ($mock) {
            $mock->shouldReceive('hasKey')->with('foo')->andReturn(true);
        });

        $controller = $this->container->getClass(FooCanaryController::class);
        
        // When accessing foo-profile
        $response = $controller->fooHandler();
        
        // Then should return FOO profile
        $this->assertEquals(['profile' => 'FOO user profile!'], $response->getResponseData());
    }

    public function test_foo_canary_controller_without_foo_in_payload()
    {
        // Given payload doesn't have 'foo' key
        $this->mock(PayloadStorage::class, function ($mock) {
            $mock->shouldReceive('hasKey')->with('foo')->andReturn(false);
        });

        $controller = $this->container->getClass(FooCanaryController::class);
        
        // When accessing foo-profile
        $response = $controller->fooHandler();
        
        // Then should return STABLE profile
        $this->assertEquals(['profile' => 'STABLE user profile.'], $response->getResponseData());
    }

    public function test_default_canary_controller()
    {
        $controller = $this->container->getClass(DefaultCanaryController::class);
        
        // When accessing default-profile
        $response = $controller->defaultHandler();
        
        // Then should return STABLE profile
        $this->assertEquals(['profile' => 'STABLE user profile.'], $response->getResponseData());
    }
} 