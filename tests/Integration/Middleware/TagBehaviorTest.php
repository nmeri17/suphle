<?php

namespace Suphle\Tests\Integration\Middleware;

use Suphle\Contracts\Config\Router as RouterContract;

use Suphle\Config\Router;

use Suphle\Request\PayloadStorage;

use Suphle\Response\Format\Json;

use Suphle\Testing\{TestTypes\ModuleLevelTest, Proxies\WriteOnlyContainer};

use Suphle\Tests\Integration\Middleware\Helpers\MocksMiddleware;

use Suphle\Tests\Mocks\Modules\ModuleOne\{Meta\ModuleOneDescriptor};

use Suphle\Tests\Mocks\Modules\ModuleOne\Coordinator\{MiddlewareCoordinator, BaseCoordinator};

use Suphle\Tests\Mocks\Modules\ModuleOne\Middlewares\{BlankMiddlewareHandler, IgnoresLowerMiddleware};

class TagBehaviorTest extends ModuleLevelTest
{
    use MocksMiddleware;

    protected function getModules(): array
    {

        return [

            $this->replicateModule(ModuleOneDescriptor::class, function (WriteOnlyContainer $container) {

                $container->replaceWithMock(RouterContract::class, Router::class, [

                    "getCoordinatorClassesToScan" => [MiddlewareCoordinator::class, BaseCoordinator::class]
                ]);
            })
        ];
    }

    public function test_parent_tag_affects_child()
    {

        // given => @see [getModules]
        // then
        $this->provideMiddleware([

            BlankMiddlewareHandler::class => $this->getMiddlewareMock(BlankMiddlewareHandler::class, 1),

            IgnoresLowerMiddleware::class => $this->getMiddlewareMock(IgnoresLowerMiddleware::class, 0)
        ]);

        $this->get("/middleware/segment"); // when
    }

    public function test_can_untag_patterns()
    {

        // given => @see [getModules]
        // then
        $this->provideMiddleware([

            BlankMiddlewareHandler::class => $this->getMiddlewareMock(BlankMiddlewareHandler::class, 0)
        ]);

        $this->get("/middleware/secede"); // when
    }

    public function test_final_middleware_has_no_request_handler()
    {

        $middlewareList = $this->getContainer()->getClass(RouterContract::class)

        ->defaultMiddleware();

        $lastMiddleware = end($middlewareList);

        $this->provideMiddleware([

            $lastMiddleware => $this->positiveDouble($lastMiddleware, [

                "process" => $this->replaceConstructorArguments(Json::class, [])
            ], [

                "process" => [1, [

                    $this->callback(fn ($subject) => $subject instanceof PayloadStorage),

                    $this->equalTo(null)
                ]]
            ]) // then
        ]);

        $this->get("/middleware/segment"); // when
    }

    public function test_can_activate_middleware()
    {

        $handlers = [IgnoresLowerMiddleware::class];

        $this->withMiddleware($handlers); // given

        $this->get("/segment") // when

        ->assertOk(); // sanity checks

        $this->assertUsedMiddleware($handlers); // then
    }

    public function test_can_deactivate_middleware()
    {
        $expectedMiddleware = [BlankMiddlewareHandler::class];

        $this->withoutMiddleware($expectedMiddleware); // given

        $this->get("/middleware/segment") // when

        ->assertOk(); // sanity checks

        $this->assertDidntUseMiddleware($expectedMiddleware); // then
    }
}
