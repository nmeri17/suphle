<?php
namespace Suphle\Events;

use Suphle\Contracts\Config\Events as EventContract;

use Suphle\Events\Attributes\{HasHandlers, EventListener};

use Suphle\Hydration\{Container, Structures\ObjectDetails};

use Suphle\Modules\Structures\ActiveDescriptors;

use Suphle\Routing\AttributeRouteScanner;

class ModuleLevelEvents
{
    protected array $handlersRegistry = [];

    protected array $firedEvents = [];

    public function __construct(
        protected readonly AttributeRouteScanner $scanner,

        protected readonly ObjectDetails $objectDetails,

        protected readonly ActiveDescriptors $descriptorsHolder
    ) {}

    public function bootReactiveLogger(): void
    {

        $this->handlersRegistry = $this->scanner->scanModulesByPath(

            function (Container $container) {

                $container->whenTypeAny()->needsAny([ // for the internal propagators

                    ModuleLevelEvents::class => $this
                ]);
                return $container->getClass(EventContract::class)
                ->getListenersPath();
            },

            $this->extractEventAttributes(...)
        );
    }

    protected function extractEventAttributes(string $handlerClass, string $moduleName): array
    {
        $handlerAttrs = $this->objectDetails->getClassAttributes($handlerClass, HasHandlers::class);

        if (empty($handlerAttrs)) return [];

        $hasHandlers = $handlerAttrs[0]->newInstance();

        $handlerMapping = [];

        foreach (
            $this->objectDetails->getMethodsWithAttributes($handlerClass, EventListener::class)
            as $method
        ) {
            $attr = $method->getAttributes(EventListener::class)[0]->newInstance();

            $handlerMapping[$attr->eventName] = $method->getName();
        }

        return [$handlerClass => [

            "emitter" => $hasHandlers->emitter,

            "handler_mapping" => $handlerMapping,

            "is_outer_module" => $hasHandlers->isOuterModule,

            "module_name" => $moduleName
        ]];
    }

    public function handleEmission(
        string $emitterModuleName, string $emitterClass,
        string $eventName, $payload
    ): void {

        $this->firedEvents[$emitterClass] = array_merge(
            $this->getLocalEntries($eventName, $emitterClass),

            $this->getExternalEntries($eventName, $emitterModuleName)
        );

        foreach ($this->firedEvents[$emitterClass] as $handlerClass => $entry)

            $this->invokeHandler($handlerClass, $eventName, $entry, $payload);
    }

    protected function getLocalEntries (string $eventName, string $emitterClass):array {

        return array_filter(

            $this->handlersRegistry, function (array $entry) use ($eventName, $emitterClass) {

                return $entry["emitter"] == $emitterClass &&

                !$entry["is_outer_module"] &&

                array_key_exists($eventName, $entry["handler_mapping"]);
        });
    }

    protected function getExternalEntries (string $eventName, string $emitterModuleName):array {

        return array_filter(

            $this->handlersRegistry, function (array $entry) use ($eventName, $emitterModuleName) {

                return $entry["emitter"] == $emitterModuleName &&

                $entry["is_outer_module"] &&

                array_key_exists($eventName, $entry["handler_mapping"]);
        });
    }

    protected function invokeHandler(string $handlerClassName, string $eventName, array $entry, $payload): void
    {
        $handlerClass = $this->descriptorsHolder->findMatchingExports($entry["module_name"])
        ->getContainer()->getClass($handlerClassName); // work with contextually relevant container

        $methodName = $entry["handler_mapping"][$eventName];

        call_user_func_array([$handlerClass, $methodName], [$payload]);
    }

    public function getFiredEvents(): array
    {
        return $this->firedEvents;
    }
}