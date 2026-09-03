<?php

namespace Suphle\Tests\Integration\Flows\Jobs\RouteBranches;

use Suphle\Flows\{ Jobs\RouteBranches, Structures\PendingFlowDetails};

use Suphle\Routing\Attributes\{FlowDefinition, HttpMethod};

use Suphle\Routing\Structures\RouteInfo;

use Suphle\Contracts\{Presentation\BaseRenderer, Config\Router as RouterContract, Modules\DescriptorInterface};

use Suphle\Contracts\Auth\{UserContract, AuthStorage};

use Suphle\Hydration\Container;

use Suphle\Response\Format\Json;

use Suphle\Config\Router;

use Suphle\Testing\Condiments\{QueueInterceptor, BaseDatabasePopulator};

use Suphle\Testing\{Proxies\SecureUserAssertions, Proxies\WriteOnlyContainer, TestTypes\ModuleLevelTest};

use Suphle\Tests\Mocks\Modules\ModuleOne\{Coordinators\FlowCoordinator, Meta\ModuleOneDescriptor};

use Suphle\Tests\Mocks\Models\Eloquent\User as EloquentUser;

use ReflectionMethod, ReflectionAttribute;

/**
 * This doesn't send the originating requests. It helps for mocking the task of an originated flow, executing that task, then verifying its behavior under certain conditions
*/
abstract class JobFactory extends ModuleLevelTest
{
    use QueueInterceptor, BaseDatabasePopulator, SecureUserAssertions {

        BaseDatabasePopulator::setUp as databaseAllSetup;
    }

    protected Container $container;

    protected EloquentUser $contentOwner, $contentVisitor;

    protected string $originDataName = "data"; // this is expected to exist in one of the module entry coordinators

    protected string $originMethod = "getCatalog"; // Default

    protected string $rendererController = FlowCoordinator::class;

    protected function setUp(): void
    {

        $this->databaseAllSetup();

        $this->catchQueuedTasks();

        $this->container = $this->firstModuleContainer();

        [$this->contentOwner, $this->contentVisitor] = $this

        ->replicator->getRandomEntities(2); // we'll visit as one of them
    }

    protected function getActiveEntity(): string
    {

        return EloquentUser::class;
    }

    protected function getInitialCount(): int
    {

        return 5;
    }

    protected function getModules ():array {

        return [
            $this->createFlowModule(ModuleOneDescriptor::class, [$this->rendererController])
        ];
    }

    protected function createFlowModule(string $descriptorname, array $coordinators):DescriptorInterface
    {

        return $this->replicateModule(
            $descriptorname,
            function (WriteOnlyContainer $container) {

                $container->replaceWithMock(RouterContract::class, Router::class, [

                    "getCoordinatorClassesToScan" => $coordinators
                ]);
            }
        );
    }

    protected function getPrecedingRenderer(): BaseRenderer
    {
        return new Json([
            $this->originDataName => [
                ["id" => 1, "name" => "Book 1"],
                ["id" => 2, "name" => "Book 2"],
                ["id" => 3, "name" => "Book 3"]
            ]
        ]);
    }

    protected function makePendingFlowDetails(?UserContract $user = null, string $storageName = null): PendingFlowDetails
    {
        $storage = $this->getAuthStorage($storageName);

        if (is_null($user)) $storage->logout();

        return $this->container->whenType(PendingFlowDetails::class)->needsAny([

            BaseRenderer::class => $this->getPrecedingRenderer(),

            RouteInfo::class => new RouteInfo(
                "catalog/{id}",
                HttpMethod::GET,
                $this->rendererController,
                $this->originMethod
            ),
            AuthStorage::class => $storage
        ])->getClass(PendingFlowDetails::class);
    }

    protected function makeRouteBranches(PendingFlowDetails $context): RouteBranches
    {

        $jobName = RouteBranches::class;

        $jobInstance = $this->container->whenType($jobName)

        ->needsArguments([ PendingFlowDetails::class => $context ])

        ->getClass($jobName);

        $this->container->refreshClass($jobName);

        return $jobInstance;
    }

    /**
     * Push in user-content/1-10, the amount returned from previous payload
    */
    protected function handleDefaultPendingFlowDetails(): PendingFlowDetails
    {

        $context = $this->makePendingFlowDetails();

        $this->makeRouteBranches($context)->handle();

        return $context;
    }
}
