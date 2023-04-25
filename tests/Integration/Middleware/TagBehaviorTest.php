<?php

namespace Suphle\Tests\Integration\Middleware;

use Suphle\Contracts\Config\Router;

use Suphle\Request\PayloadStorage;

use Suphle\Response\Format\Json;

use Suphle\Testing\{TestTypes\ModuleLevelTest, Proxies\WriteOnlyContainer};

use Suphle\Tests\Integration\Middleware\Helpers\MocksMiddleware;

use Suphle\Tests\Mocks\Modules\ModuleOne\{Meta\ModuleOneDescriptor, Routes\Middlewares\MultiTagSamePattern, Config\RouterMock};

use Suphle\Tests\Mocks\Modules\ModuleOne\Middlewares\{BlankMiddlewareHandler, BlankMiddleware2Handler, BlankMiddleware3Handler};

class TagBehaviorTest extends ModuleLevelTest
{
    use MocksMiddleware;

    protected function getModules(): array
    {

        return [

            $this->replicateModule(ModuleOneDescriptor::class, function (WriteOnlyContainer $container) {

                $container->replaceWithMock(Router::class, RouterMock::class, [

                    "browserEntryRoute" => MultiTagSamePattern::class
                ]);
            })
        ];
    }

    public function test_multi_patterns_to_tag_should_work()
    {

        // given => @see [getModules]
        // then
        $this->provideMiddleware([

            BlankMiddlewareHandler::class => $this->getMiddlewareMock(BlankMiddlewareHandler::class, 1),

            BlankMiddleware2Handler::class => $this->getMiddlewareMock(BlankMiddleware2Handler::class, 0)
        ]);

        $this->get("/first-single"); // when
    }

    public function test_parent_tag_affects_child()
    {

        // given => @see [getModules]
        // then
        $this->provideMiddleware([

            BlankMiddlewareHandler::class => $this->getMiddlewareMock(BlankMiddlewareHandler::class, 1),

            BlankMiddleware2Handler::class => $this->getMiddlewareMock(BlankMiddleware2Handler::class, 0)
        ]);

        $this->get("/fifth-single/segment"); // when
    }

    public function test_can_untag_patterns()
    {

        // given => @see [getModules]
        // then
        $this->provideMiddleware([

            BlankMiddleware3Handler::class => $this->getMiddlewareMock(BlankMiddleware3Handler::class, 0)
        ]);

        $this->get("/fourth-single/second-untag"); // when
    }

    public function test_final_middleware_has_no_request_handler()
    {

        $middlewareList = $this->getContainer()->getClass(Router::class)

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

        $this->get("/first-single"); // when
    }
}
