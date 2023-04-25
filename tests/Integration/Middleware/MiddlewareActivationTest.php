<?php

namespace Suphle\Tests\Integration\Middleware;

use Suphle\Contracts\Config\Router;

use Suphle\Tests\Mocks\Models\Eloquent\User as EloquentUser;

use Suphle\Testing\{ TestTypes\ModuleLevelTest, Condiments\BaseDatabasePopulator };

use Suphle\Testing\Proxies\{ WriteOnlyContainer, SecureUserAssertions };

use Suphle\Tests\Integration\Middleware\Helpers\MocksMiddleware;

use Suphle\Tests\Mocks\Modules\ModuleOne\{ Meta\ModuleOneDescriptor, Config\RouterMock};

use Suphle\Tests\Mocks\Modules\ModuleOne\Middlewares\{ BlankMiddlewareHandler, BlankMiddleware2Handler};

use Suphle\Tests\Mocks\Modules\ModuleOne\Middlewares\Collectors\{BlankCollectionMetaFunnel, BlankMiddleware2Collector};

use Suphle\Tests\Mocks\Modules\ModuleOne\Routes\Prefix\{ActualEntry, Secured\MisleadingEntry};

class MiddlewareActivationTest extends ModuleLevelTest
{
    use BaseDatabasePopulator, SecureUserAssertions, MocksMiddleware {

        BaseDatabasePopulator::setUp as databaseAllSetup;
    }

    protected bool $debugCaughtExceptions = true;

    private string $threeTierUrl = "/first/middle/without";

    private $contentVisitor;

    protected function setUp(): void
    {

        $this->databaseAllSetup();

        $this->contentVisitor = $this->replicator->getRandomEntity();
    }

    protected function getModules(): array
    {

        return [

            $this->replicateModule(ModuleOneDescriptor::class, function (WriteOnlyContainer $container) {

                $container->replaceWithMock(Router::class, RouterMock::class, [

                    "browserEntryRoute" => MisleadingEntry::class
                ]);
            })
        ];
    }

    protected function getActiveEntity(): string
    {

        return EloquentUser::class;
    }

    public function test_can_activate_middleware()
    {

        $this->actingAs($this->contentVisitor);

        $handlerName = BlankMiddleware2Handler::class;

        $this->provideMiddleware([ // then 1

            $handlerName => $this->getMiddlewareMock($handlerName, 1)
        ]);

        $collectorName = BlankMiddleware2Collector::class;

        $this->withMiddleware([new $collectorName(["WITHOUT"])]); // given

        $this->get($this->threeTierUrl) // when

        ->assertOk(); // sanity checks

        $this->assertUsedCollectorNames([$collectorName]); // then 2
    }

    public function test_can_deactivate_middleware()
    {

        $this->actingAs($this->contentVisitor);

        $handlerName = BlankMiddlewareHandler::class;

        $this->provideMiddleware([ // then 1

            $handlerName => $this->getMiddlewareMock($handlerName, 0)
        ]);

        $expectedMiddleware = [BlankCollectionMetaFunnel::class]; // can actually be found at the route

        $this->withoutMiddleware($expectedMiddleware); // given

        $this->get($this->threeTierUrl) // when

        ->assertOk(); // sanity checks

        $this->assertDidntUseCollectorNames($expectedMiddleware); // then 2
    }
}
