<?php

namespace Suphle\Tests\Integration\Middleware;

use Suphle\Contracts\Config\Router;

use Suphle\Middleware\Handlers\FinalHandlerWrapper;

use Suphle\Response\Format\Json;

use Suphle\Testing\{ TestTypes\ModuleLevelTest, Proxies\WriteOnlyContainer };

use Suphle\Tests\Mocks\Modules\ModuleOne\{Meta\ModuleOneDescriptor, Config\RouterMock, Middlewares\AlterFinalResponse};

class AlterFinalResponseTest extends ModuleLevelTest
{
    protected function getModules(): array
    {

        return [
            $this->replicateModule(ModuleOneDescriptor::class, function (WriteOnlyContainer $container) {

                $finalName = FinalHandlerWrapper::class;

                $container->replaceWithMock(Router::class, RouterMock::class, [

                    "defaultMiddleware" => [
                        AlterFinalResponse::class,

                        $finalName
                    ]
                ])
                ->replaceWithMock($finalName, $finalName, [

                    "process" => (new Json(""))->setRawResponse(["foo" => "bar"])
                ], [], false);
            })
        ];
    }

    public function test_middleware_can_alter_response()
    {

        // given @see module injection

        $this->get("/segment") // when

        ->assertJson(["foo" => "baz"]); // then
    }
}
