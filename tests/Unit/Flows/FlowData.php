<?php
namespace Suphle\Tests\Unit\Flows;

use Suphle\Routing\Attributes\{CollectionFlow, CollectionFlowOperation, SingleFlowOperation, SingleFlow};

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

    protected function createCollectionFlow(CollectionFlowOperation $operation, int $maxHits = 1): CollectionFlow
    {
        return new CollectionFlow(
            target: "symbols/{id}/chart",
            source: $this->payloadKey,
            operation: $operation,
            columnName: $this->columnName,
            maxHits: $maxHits
        );
    }

    protected function payloadFromPrevious(): array
    {
        return [
            $this->payloadKey => $this->indexesToModels()
        ];
    }
}