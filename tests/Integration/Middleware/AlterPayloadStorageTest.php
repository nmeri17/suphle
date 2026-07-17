<?php

namespace Suphle\Tests\Integration\Middleware;

use Suphle\Contracts\Config\Router as RouterContract;

use Suphle\Config\Router;

use Suphle\Middleware\Handlers\FinalHandlerWrapper;

use Suphle\Testing\{ TestTypes\ModuleLevelTest, Proxies\WriteOnlyContainer };

use Suphle\Tests\Mocks\Modules\ModuleOne\{Meta\ModuleOneDescriptor, Middlewares\AltersPayloadStorage};

class AlterPayloadStorageTest extends ModuleLevelTest
{
    protected string $modifierMiddleware = AltersPayloadStorage::class;

    protected function getModules(): array
    {

        return [
            $this->replicateModule(ModuleOneDescriptor::class, function (WriteOnlyContainer $container) {

                $container->replaceWithMock(RouterContract::class, Router::class, [

                    "defaultMiddleware" => [
                        $this->modifierMiddleware,

                        FinalHandlerWrapper::class
                    ]
                ]);
            })
        ];
    }

    // this works because of object references. Changes to payloadStorage within the middleware affect the one stored in container
    public function test_container_must_not_provide_altered_payloadStorage()
    {
// find the cdtr this route belongs to
        $response = $this->get("/all-payload"); // when

        $middlewareInstance = $this->getContainer()->getClass($this->modifierMiddleware);

        $response->assertJson($middlewareInstance->payloadUpdates()); // then
    }
}
