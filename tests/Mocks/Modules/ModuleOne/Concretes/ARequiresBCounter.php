<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Concretes;

use Suphle\Hydration\Container;

use Suphle\Tests\Mocks\Modules\ModuleOne\Interfaces\CInterface;

class ARequiresBCounter
{
    private $cInterface;

    public function __construct(protected BCounter $b1, protected readonly string $primitive)
    {

        //
    }

    public function getConstructorB(): BCounter
    {

        return $this->b1;
    }

    public function getInternalB(Container $container): BCounter
    {

        return $container->getClass(BCounter::class);
    }

    public function getPrimitive(): string
    {

        return $this->primitive;
    }

    public function receiveBCounter(BCounter $injected): void
    {

        $this->b1 = $injected;
    }

    public function receiveProvidedInterface(CInterface $injected): void
    {

        $this->cInterface = $injected;
    }

    public function getCInterface(): CInterface
    {

        return $this->cInterface;
    }
}
