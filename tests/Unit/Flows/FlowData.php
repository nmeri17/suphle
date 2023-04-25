<?php

namespace Suphle\Tests\Unit\Flows;

use Suphle\Flows\Previous\CollectionNode;

trait FlowData
{
    protected $payloadKey = "data";
    protected $columnName = "id";
    protected $indexes;

    protected function getIndexes(): array
    {

        $indexes = [];

        for ($i=1; $i < 11; $i++) {
            $indexes[] = $i;
        }

        return $indexes;
    }

    protected function indexesToModels(): array
    {

        return array_map(fn ($id) => compact("id"), $this->indexes);
    }

    protected function createCollectionNode(): CollectionNode
    {

        return new CollectionNode($this->payloadKey, $this->columnName);
    }

    protected function payloadFromPrevious(): array
    {

        return [ // should this be returned, or the models, directly

            $this->payloadKey => $this->indexesToModels()
        ];
    }
}
