<?php
namespace Suphle\Tests\Unit\Flows;

use Suphle\Routing\Attributes\{CollectionFlow, CollectionFlowOperation};

trait FlowData
{
    protected string $payloadKey = "data";
    protected string $columnName = "id";
    protected array $indexes;

    protected function getIndexes(): array
    {
        return range(1, 10);
    }

    protected function indexesToModels(): array
    {
        return array_map(fn ($id) => ["id" => $id], $this->indexes);
    }

    protected function createCollectionFlow(CollectionFlowOperation $operation): CollectionFlow
    {
        return new CollectionFlow(
            target: "symbols/{id}/chart",
            source: $this->payloadKey,
            operation: $operation,
            columnName: $this->columnName
        );
    }

    protected function payloadFromPrevious(): array
    {
        return [
            $this->payloadKey => $this->indexesToModels()
        ];
    }
}