<?php

namespace Suphle\Routing\Structures;

use Suphle\Routing\CollectionMetaFunnel;

trait ReceivesMetaFunnel
{
    protected array $metaFunnels = [];

    public function addMetaFunnel(CollectionMetaFunnel $metaFunnel): void
    {

        $this->metaFunnels[] = $metaFunnel;
    }
}
