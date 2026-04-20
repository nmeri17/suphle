<?php

namespace Suphle\Tests\Integration\Flows\Jobs\RouteBranches;

use Suphle\Flows\{ Jobs\RouteBranches, Structures\PendingFlowDetails};

use Suphle\Routing\Attributes\FlowDefinition;

use Suphle\Contracts\{Auth\UserContract, Presentation\BaseRenderer, Database\OrmDialect};

use Suphle\Hydration\Container;

use Suphle\Response\Format\Json;

use Suphle\Testing\Condiments\{QueueInterceptor, BaseDatabasePopulator};

use Suphle\Testing\Proxies\SecureUserAssertions;

use Suphle\Tests\Integration\Modules\ModuleDescriptor\DescriptorCollection;

use Suphle\Tests\Mocks\Modules\ModuleOne\{Coordinators\FlowCoordinator, Concretes\Services\DummyModels};

use Suphle\Tests\Mocks\Models\Eloquent\User as EloquentUser;

use ReflectionMethod, ReflectionAttribute;

/**
 * This doesn't send the originating requests. It helps for mocking the task of an originated flow, executing that task, then verifying its behavior under certain conditions
*/
abstract class JobFactory extends DescriptorCollection
{
    use QueueInterceptor, BaseDatabasePopulator, SecureUserAssertions {

        BaseDatabasePopulator::setUp as databaseAllSetup;
    }

    protected Container $container;

    protected EloquentUser $contentOwner;

    protected EloquentUser $contentVisitor;

    protected string $userUrl = "/user-content/5";

    protected string // corresponds to the content generated after using [flowUrl] to create a context
    $flowUrl = "user-content/id";

    protected string // this is expected to exist in one of the module entry collections
    $originDataName = "all_users";

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

    protected function getPrecedingRenderer(): BaseRenderer
    {
        return $this->positiveDouble(Json::class, [
            "getRawResponse" => [
                $this->originDataName => [
                    ["id" => 1, "name" => "Book 1"], // Mock data
                    ["id" => 2, "name" => "Book 2"]
                ]
            ],// these are no longer read from here
            // Pull the real #[CollectionFlow] or #[SingleFlow] from the coordinator
            "getFlows" => $this->extractAttributes(FlowCoordinator::class, $this->originMethod),
            "getCoordinator" => $this->positiveDouble(FlowCoordinator::class),
            "getHandler" => $this->originMethod
        ]);
    }

    protected function extractAttributes(string $class, string $method): array
    {
        $reflection = new ReflectionMethod($class, $method);
        return array_map(
            fn($attr) => $attr->newInstance(),
            $reflection->getAttributes(FlowDefinition::class, ReflectionAttribute::IS_INSTANCEOF)
        );
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

    protected function makePendingFlowDetails(?UserContract $user = null, string $storageName = null): PendingFlowDetails
    {

        $storage = $this->getAuthStorage($storageName);

        if (!is_null($user)) {

            $storage->startSession($user->getId()); // creates a collection of 10 models in preceding renderer, then assigns the given user as their owner in the flow we are going to make

            $storage->setHydrator($this->container->getClass(
                OrmDialect::class
            )->getUserHydrator());
        } else {
            $storage->logout();
        }

        return new PendingFlowDetails(
            $this->getPrecedingRenderer(),
            $storage
        );
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
