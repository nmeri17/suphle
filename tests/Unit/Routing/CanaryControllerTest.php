<?php

namespace Suphle\Tests\Unit\Routing;

use Suphle\Testing\TestTypes\IsolatedComponentTest;
use Suphle\Tests\Integration\Generic\CommonBinds;
use Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators\{User5CanaryController, FooCanaryController, DefaultCanaryController};
use Suphle\Request\RequestDetails;
use Suphle\Request\PayloadStorage;

/**
 * Unit tests for Canary coordinator branching logic.
 *
 * These tests use IsolatedComponentTest so the Container is available without
 * a full module boot. We stub RequestDetails::getCanaryState() to simulate
 * which canary evaluator matched, then assert the coordinator returns the
 * expected JSON payload for that state.
 */
class CanaryControllerTest extends IsolatedComponentTest
{
    use CommonBinds;

    protected bool $usesRealDecorator = false;

    // ---- User5CanaryController ----

    public function test_user5_coordinator_returns_user5_profile_for_user5_canary()
    {
        // Given: stub canary state to 'user5'
        $this->massProvide([
            RequestDetails::class => $this->positiveDouble(RequestDetails::class, [
                'getCanaryState' => 'user5',
            ])
        ]);

        $controller = $this->container->getClass(User5CanaryController::class);

        // When
        $response = $controller->user5Handler();

        // Then
        $this->assertEquals(['profile' => 'USER5 user profile!'], $response->getRawResponse());
    }

    public function test_user5_coordinator_returns_stable_profile_for_null_canary()
    {
        $this->massProvide([
            RequestDetails::class => $this->positiveDouble(RequestDetails::class, [
                'getCanaryState' => null,
            ])
        ]);

        $controller = $this->container->getClass(User5CanaryController::class);

        $response = $controller->user5Handler();

        $this->assertEquals(['profile' => 'STABLE user profile.'], $response->getRawResponse());
    }

    // ---- FooCanaryController ----

    public function test_foo_coordinator_returns_foo_profile_when_canary_is_foo()
    {
        $this->massProvide([
            RequestDetails::class => $this->positiveDouble(RequestDetails::class, [
                'getCanaryState' => 'foo',
            ])
        ]);

        $controller = $this->container->getClass(FooCanaryController::class);

        $response = $controller->fooHandler();

        $this->assertEquals(['profile' => 'FOO user profile!'], $response->getRawResponse());
    }

    public function test_foo_coordinator_returns_stable_profile_when_canary_is_null()
    {
        $this->massProvide([
            RequestDetails::class => $this->positiveDouble(RequestDetails::class, [
                'getCanaryState' => null,
            ])
        ]);

        $controller = $this->container->getClass(FooCanaryController::class);

        $response = $controller->fooHandler();

        $this->assertEquals(['profile' => 'STABLE user profile.'], $response->getRawResponse());
    }

    // ---- DefaultCanaryController ----

    public function test_default_coordinator_always_returns_stable_profile()
    {
        $controller = $this->container->getClass(DefaultCanaryController::class);

        $response = $controller->defaultHandler();

        $this->assertEquals(['profile' => 'STABLE user profile.'], $response->getRawResponse());
    }
}