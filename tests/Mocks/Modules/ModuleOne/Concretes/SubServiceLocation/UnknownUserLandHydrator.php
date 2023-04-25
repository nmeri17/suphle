<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Concretes\SubServiceLocation;

use Suphle\Tests\Mocks\Modules\ModuleOne\Concretes\BCounter;

class UnknownUserLandHydrator extends HydratorConsumer
{
    public function getParentsBCounter(): BCounter
    {

        return $this->container->getClass(BCounter::class, true);
    }

    public function getSelfBCounter(): BCounter
    {

        return $this->container->getClass(BCounter::class); // unable to see x or y
    }
}
