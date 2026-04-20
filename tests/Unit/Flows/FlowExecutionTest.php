<?php
namespace Suphle\Tests\Unit\Flows;

use Suphle\Flows\{FlowHydrator, OuterFlowWrapper, UmbrellaSaver};
use Suphle\Routing\Attributes\{CollectionFlow, CollectionFlowOperation};
use Suphle\Flows\Structures\{GeneratedUrlExecution, PendingFlowDetails};
use Suphle\Testing\TestTypes\IsolatedComponentTest;
use Suphle\Tests\Integration\Generic\CommonBinds;

class FlowExecutionTest extends IsolatedComponentTest
{
    use FlowData, CommonBinds;

    private string $sutName = FlowHydrator::class;
    private $flowDetails;

    public function setUp(): void
    {
        parent::setUp();
        $this->indexes = $this->getIndexes();
        $this->flowDetails = $this->replaceConstructorArguments(PendingFlowDetails::class, [], [
            "getStoredUserId" => OuterFlowWrapper::ALL_USERS
        ]);
    }

    /**
     * This single test replaces the old FlowConfigTest.
     * It ensures the Hydrator reads #[CollectionFlow] and passes the 
     * correct maxHits/TTL down to the UmbrellaSaver.
     */
    public function test_runAttribute_configures_and_executes()
    {
        // 1. GIVEN: An attribute with specific hits/ttl
        $maxHits = 10;
        $flow = $this->createCollectionFlow(CollectionFlowOperation::PIPE_TO);
        // We can't use readonly props in mocks easily, so we use the real attribute
        $flow = new CollectionFlow(
            target: $flow->target,
            source: $flow->source,
            operation: $flow->operation,
            maxHits: $maxHits
        );

        // 2. THEN: Verify the Saver receives a RouteUserNode with our maxHits
        $hydrator = $this->replaceConstructorArguments($this->sutName, [], [
            "handlePipe" => [$this->positiveDouble(GeneratedUrlExecution::class)]
        ], [
            "handlePipe" => [1, $this->anything()]
        ]);

        $this->decorateHydrator($hydrator, [
            UmbrellaSaver::class => $this->positiveDouble(UmbrellaSaver::class, [], [
                "saveNewUmbrella" => [count($this->indexes), $this->callback(function ($path, $node) use ($maxHits) {
                    return $node->getMaxHits() === $maxHits; // Verify config survived
                })]
            ])
        ]);

        $hydrator->setRequestDetails($this->payloadFromPrevious(), "symbols/{id}/chart");

        // 3. WHEN
        $hydrator->runAttribute($flow, $this->flowDetails);
    }

    /**
     * Data provider approach to ensure every Enum case 
     * hits the correct internal handler.
     * @dataProvider getFlowAttributeMapping
     */
    public function test_runAttribute_triggers_correct_handler($flow, string $expectedHandler)
    {
        $hydrator = $this->replaceConstructorArguments($this->sutName, [], [
            $expectedHandler => [$this->positiveDouble(GeneratedUrlExecution::class)]
        ], [
            $expectedHandler => [1, $this->anything()]
        ]);

        $hydrator->setRequestDetails($this->payloadFromPrevious(), "target/path");
        $this->decorateHydrator($hydrator)->runAttribute($flow, $this->flowDetails);
    }

    public function getFlowAttributeMapping(): array
    {
        return [
            [$this->createCollectionFlow(CollectionFlowOperation::PIPE_TO), "handlePipe"],
            [$this->createCollectionFlow(CollectionFlowOperation::AS_ONE), "handleAsOne"]
        ];
    }
}