<?php
namespace Suphle\Tests\Unit\Flows;

use Suphle\Flows\{FlowHydrator, OuterFlowWrapper, UmbrellaSaver};

use Suphle\Routing\{Structures\RouteInfo, Attributes\HttpMethod};

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

        $flow = $this->createCollectionFlow(CollectionFlowOperation::PIPE_TO, $maxHits);

        // then
        $umbrella = $this->replaceConstructorArguments(UmbrellaSaver::class, [], [], [ // Verify the Saver receives a RouteUserNode with our maxHits
            "saveNewUmbrella" => [
                count($this->indexes), [
                    $this->anything(),
                    $this->callback(function ($node) {

                        return !$node->hasExceededMaxHits(); // Verify config survived
                    }),
                    $this->anything()
                ]
            ]
        ]);
        $hydrator = $this->replaceConstructorArguments($this->sutName, [
                UmbrellaSaver::class => $umbrella
            ], [
            "handlePipe" => [
                $this->replaceConstructorArguments(GeneratedUrlExecution::class, [])
            ]
        ], [
            "handlePipe" => [1, [$this->anything()]]
        ]);

        $this->decorateHydrator($hydrator, "symbols/{id}/chart");

        $hydrator->runAttribute($flow, $this->flowDetails); // when
    }

    public function test_runAttribute_triggers_correct_handler()
    {
        $this->dataProvider([

            $this->getFlowAttributeMapping(...)
        ], function (CollectionFlow $flow, string $expectedHandler) {

            $hydrator = $this->replaceConstructorArguments($this->sutName, [
                UmbrellaSaver::class => $this->replaceConstructorArguments(UmbrellaSaver::class, [])
            ], [
                
                $expectedHandler => [

                    $this->replaceConstructorArguments(GeneratedUrlExecution::class, [])
                ]
            ], [
                $expectedHandler => [1, [$this->anything()]]
            ]);

            $this->decorateHydrator($hydrator, "");

            $hydrator->runAttribute($flow, $this->flowDetails);
        });
    }

    public function getFlowAttributeMapping(): array
    {
        return [
            [$this->createCollectionFlow(CollectionFlowOperation::PIPE_TO), "handlePipe"],
            [$this->createCollectionFlow(CollectionFlowOperation::AS_ONE), "handleAsOne"]
        ];
    }
    protected function decorateHydrator (FlowHydrator $hydrator, string $urlPattern):void {

        $hydrator->setContainer($this->getContainer());

        $hydrator->setRequestDetails(
            $this->payloadFromPrevious(),

            $this->replaceConstructorArguments(RouteInfo::class, [
                "path" => $urlPattern,
                "method" => HttpMethod::GET
            ])
        );
    }
}