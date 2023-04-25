<?php

namespace Suphle\Server;

use Suphle\Server\Structures\DependencyRule;

use Suphle\File\{FileSystemReader, SetsExecutionPath};

use Suphle\Hydration\{Container, Structures\ObjectDetails};

use Suphle\Contracts\{Queues\Task, Modules\ControllerModule, Server\DependencyFileHandler};

use Suphle\IO\{Http\BaseHttpRequest, Mailing\MailBuilder};

use Suphle\Request\PayloadStorage;

use Suphle\Services\{ServiceCoordinator, UpdatefulService, UpdatelessService, ConditionalFactory};

use Suphle\Services\Structures\{ModelfulPayload, ModellessPayload};

use Suphle\Services\DependencyRules\{OnlyLoadedByHandler, ActionDependenciesValidator, ServicePreferenceHandler};

class DependencySanitizer
{
    use SetsExecutionPath;

    protected array $rules = [];

    public function __construct(
        protected readonly FileSystemReader $fileSystemReader,
        protected readonly Container $container,
        protected readonly ObjectDetails $objectMeta
    ) {

        //
    }

    protected function setDefaultRules(): void
    {

        $this->coordinatorConstructor();

        $this->coordinatorActionMethods();

        $this->protectUpdateyServices();

        $this->protectMailBuilders();
    }

    public function cleanseConsumers(): void
    {

        if (empty($this->rules)) {
            $this->setDefaultRules();
        }

        $hydratedHandlers = array_map(function ($rule) {

            return $rule->extractHandler($this->container);
        }, $this->rules);

        foreach ($hydratedHandlers as $index => $handler) {

            $this->iterateExecutionPath(
                $this->executionPath,
                $handler,
                $this->rules[$index]
            );
        }
    }

    protected function iterateExecutionPath(
        string $executionPath,
        DependencyFileHandler $handler,
        DependencyRule $dependencyRule
    ): void {

        $this->fileSystemReader->iterateDirectory(
            $executionPath,
            function ($directoryPath, $directoryName) use ($handler, $dependencyRule) {

                $this->iterateExecutionPath(
                    $directoryPath,
                    $handler,
                    $dependencyRule
                );
            },
            function ($filePath, $fileName) use ($handler, $dependencyRule) {

                $classFullName = $this->objectMeta->classNameFromFile($filePath);

                if (
                    !empty($classFullName) &&

                    $dependencyRule->shouldEvaluateClass($classFullName)
                ) {

                    $handler->evaluateClass($classFullName);
                }
            },
            function ($path) {

                //
            }
        );
    }

    public function coordinatorConstructor(array $toOmit = []): void
    {

        $this->addRule(
            ServicePreferenceHandler::class,
            function ($className) use ($toOmit) {

                return $this->coordinatorFilter($className) &&

                !in_array($className, $toOmit);
            },
            [
                ConditionalFactory::class, // We're treating it as a type of service in itself
                ControllerModule::class, // These are a service already. There's no need accessing them through another local proxy

                PayloadStorage::class, // there may be items we don't want to pass to the builder but to a service?

                BaseHttpRequest::class, UpdatefulService::class,

                UpdatelessService::class
            ]
        );
    }

    public function coordinatorActionMethods(): void
    {

        $this->addRule(
            ActionDependenciesValidator::class,
            $this->coordinatorFilter(...),
            [

                ModelfulPayload::class, ModellessPayload::class
            ]
        );
    }

    protected function coordinatorFilter(string $className): bool
    {

        return $this->objectMeta->stringInClassTree(
            $className,
            ServiceCoordinator::class
        );
    }

    public function addRule(string $ruleHandler, callable $filter, array $argumentList): void
    {

        $this->rules[] = new DependencyRule($ruleHandler, $filter, $argumentList);
    }

    protected function protectUpdateyServices(): void
    {

        $this->addRule(
            ServicePreferenceHandler::class,
            function ($className): bool {

                return $this->objectMeta->stringInClassTree(
                    $className,
                    UpdatefulService::class
                );
            },
            [UpdatelessService::class]
        );

        $this->addRule(
            ServicePreferenceHandler::class,
            function ($className): bool {

                return $this->objectMeta->stringInClassTree(
                    $className,
                    UpdatelessService::class
                );
            },
            [UpdatefulService::class]
        );
    }

    public function protectMailBuilders(array $toOmit = []): void
    {

        $this->addRule(
            OnlyLoadedByHandler::class,
            function ($className) use ($toOmit): bool {

                return !in_array($className, $toOmit);
            },
            [MailBuilder::class, [Task::class]]
        );
    }
}
