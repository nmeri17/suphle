<?php
namespace Suphle\Tests\Unit\Flows;

use Suphle\Flows\FlowHydrator;
use Suphle\Routing\Attributes\{CollectionFlow, CollectionFlowOperation};
use Suphle\Flows\Structures\GeneratedUrlExecution;
use Suphle\Testing\TestTypes\IsolatedComponentTest;
use Suphle\Tests\Integration\Generic\CommonBinds;

class HandlersTest extends IsolatedComponentTest
{
    use FlowData, CommonBinds;

    private string $sutName = FlowHydrator::class;

    public function test_handlePipe_updates_placeholders_per_index()
    {
        $indexes = $this->getIndexes();
        $column = "id";

        // then
        $sut = $this->replaceConstructorArguments($this->sutName, [], [
            "updatePlaceholders" => $this->returnSelf(),
            "executeGeneratedUrl" => $this->positiveDouble(GeneratedUrlExecution::class)
        ], [
            "updatePlaceholders" => [count($indexes), $this->anything()],
            "executeGeneratedUrl" => [count($indexes), []]
        ]);

        // when
        $sut->handlePipe($indexes, $column);
    }

    public function test_handleAsOne_joins_indexes()
    {
        $indexes = [1, 2, 3];
        $column = "ids";

        // then
        $sut = $this->replaceConstructorArguments($this->sutName, [], [
            "updatePlaceholders" => $this->returnSelf(),
            "executeGeneratedUrl" => $this->positiveDouble(GeneratedUrlExecution::class)
        ], [
            "updatePlaceholders" => [1, [["ids" => "1,2,3"]]]
        ]);

        // when
        $sut->handleAsOne($indexes, $column);
    }
}