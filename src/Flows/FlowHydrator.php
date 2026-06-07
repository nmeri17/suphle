<?php
namespace Suphle\Flows;

use Suphle\Flows\Structures\{RouteUserNode, ServiceContext, GeneratedUrlExecution, PendingFlowDetails, RangeContext};

use Suphle\Routing\Attributes\{FlowDefinition, CollectionFlow, SingleFlow, CollectionFlowOperation, SingleFlowOperation};

use Suphle\Routing\{RouteInfoExecutor, Structures\RouteInfo};

use Suphle\Services\Decorators\VariableDependenciesHandler;
use Suphle\Hydration\Container;
use Suphle\Request\PayloadStorage;

use Illuminate\Support\Arr;

use Exception;
use Throwable;

#[VariableDependenciesHandler([
    "setContainer",
    "setPayloadStorage"
])]
class FlowHydrator
{
    protected PayloadStorage $payloadStorage;

    protected Container $container;

    protected RouteInfo $routeDetails;

    protected iterable $previousResponse;

    public function __construct(
        protected readonly UmbrellaSaver $flowSaver
    ) {

        //
    }

    public function setContainer(Container $container): void
    {

        $this->container = $container;
    }

    public function setPayloadStorage(PayloadStorage $payloadStorage): void
    {

        $this->payloadStorage = $payloadStorage;
    }

    public function setRequestDetails(iterable $previousResponse, RouteInfo $routeDetails ): void {

        $this->previousResponse = $previousResponse;

        $this->routeDetails = $routeDetails;
    }

    // for given url, generate all renderer units and lodge in new umbrella
    public function runAttribute(
        FlowDefinition $flow,
        PendingFlowDetails $originatingFlowDetails
    ): void {

        $this->bindObjectsForUser($originatingFlowDetails);

        $generatedRenderers = match (true) {
            $flow instanceof CollectionFlow => $this->handleCollection($flow),

            $flow instanceof SingleFlow => $this->handleSingle($flow),

            default => throw new Exception("Unknown Flow Type")
        };

        foreach ($generatedRenderers as $generationUnit) {

            if (!$generationUnit) continue;

            $unitPayload = new RouteUserNode(

                $generationUnit->getRenderer(), $this->routeDetails
            );

            $unitPayload->setMetaDetails($flow->ttl, $flow->maxHits);

            $this->flowSaver->saveNewUmbrella(
                $generationUnit->getRequestPath(),
                $unitPayload,
                $originatingFlowDetails
            );
        }
    }

    protected function handleCollection(CollectionFlow $flow): array
    {

        $data = Arr::get(
            $this->previousResponse, $flow->source
        );

        if (is_null($data))
            return [];

        return match ($flow->operation) {
            CollectionFlowOperation::PIPE_TO =>
                $this->handlePipe($data, $flow->columnName),

            CollectionFlowOperation::AS_ONE =>
                $this->handleAsOne($data, $flow->columnName),

            CollectionFlowOperation::SET_FROM_SERVICE =>
                $this->handleServiceSource($data, $flow),

            CollectionFlowOperation::RANGE =>
                $this->handleRange($data, $flow),

            default => []
        };
    }

    public function handleSingle(SingleFlow $flow): array
    {

        $value = Arr::get(
            $this->previousResponse,
            $flow->source
        );

        if ($flow->operation === SingleFlowOperation::ALTERS_QUERY) {

            $queryPart = parse_url($value, PHP_URL_QUERY);

            parse_str($queryPart, $queryArray);

            $generated = $this->generateWithPayload($queryArray);

            if ($generated) {

                $generated->changeUrl($value);

                return [$generated];
            }

            return [];
        }

        return [];
    }

    public function handlePipe(
        array $indexes,
        string $column
    ): array {

        return array_values(array_filter(
            array_map(function ($value) use ($column) {

                return $this->generateWithPlaceholders([
                    $column => $value
                ]);

            }, $indexes)
        ));
    }

    public function handleAsOne(
        array $indexes,
        string $column
    ): array {

        $generated = $this->generateWithPlaceholders([
            $column . "s" => implode(",", $indexes)
        ]);

        return [$generated];
    }

    /**
     * @return GeneratedUrlExecution[]
    */
    public function handleRange(
        iterable $indexes,
        CollectionFlow $flow
    ): array {

        $context = $flow->rangeContext;

        if ($context->isDateMode)
            return $this->handleDateRange($indexes, $context);

        $generatedContent = $this->generateWithPlaceholders([

            $context->getParameterMax => max($indexes),
            $context->getParameterMin => min($indexes)
        ]);

        return [$generatedContent];
    }

    /**
     * @return GeneratedUrlExecution[]
    */
    public function handleDateRange(
        array $indexes,
        RangeContext $context
    ): array {

        usort($indexes, function ($a, $b) {

            return strtotime($a) - strtotime($b); // asc
        });

        $generatedContent = $this->generateWithPlaceholders([

            $context->getParameterMin => $indexes[0], // use `current` here instead?
            $context->getParameterMax => end($indexes)
        ]);

        return [$generatedContent];
    }

    protected function handleServiceSource(
        array $data,
        CollectionFlow $flow
    ): array {

        $context = $flow->serviceContext;

        $service = $this->container->getClass(
            $context->serviceName
        );

        $renderer = call_user_func(
            [$service, $context->method],
            $data
        );

        return []; //[new GeneratedUrlExecution(some url, $renderer)]; // don't know what url to assign to this cuz idk what operation we're performing on the node
    }

    public function executeGeneratedUrl(): ?GeneratedUrlExecution
    {

        try {

            $renderer = $this->container
                ->getClass(RouteInfoExecutor::class)
                ->handleFoundRoute($this->routeDetails);

            return new GeneratedUrlExecution(
                $this->routeDetails->getPathFromStack(),
                $renderer
            );

        } catch (Throwable) {

            return null;
        }
    }

    protected function generateWithPlaceholders(
        array $updates
    ): ?GeneratedUrlExecution {

        return $this
            ->updatePlaceholders($updates)
            ->executeGeneratedUrl();
    }

    protected function generateWithPayload(
        array $updates
    ): ?GeneratedUrlExecution {

        return $this
            ->updatePayloadStorage($updates)
            ->executeGeneratedUrl();
    }

    protected function updatePlaceholders(array $updates): self
    {

        $this->routeDetails->setSegmentValues($updates);

        return $this;
    }

    protected function updatePayloadStorage(array $updates): self
    {

        $this->payloadStorage->mergePayload($updates);

        return $this;
    }

    protected function bindObjectsForUser(
        PendingFlowDetails $originatingFlowDetails
    ): void {

        $storageInstance = $this->container->getClass(
            $originatingFlowDetails->authStorage::class
        );

        if ($originatingFlowDetails->getStoredUserId() !== '*')
            $storageInstance->imitate(
                $originatingFlowDetails->getStoredUserId()
            );
    }
}