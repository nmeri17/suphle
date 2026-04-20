<?php

namespace Suphle\Services\Structures;

use Suphle\Contracts\Database\OrmDialect;

use Suphle\Services\Decorators\VariableDependencies;

use Suphle\Request\PayloadStorage;

use Suphle\Routing\Structures\RouteInfo;

#[VariableDependencies([

    "setPayloadStorage", "setRouteInfo",

    "setOrmDialect"
])]
abstract class ModelfulPayload
{
    protected PayloadStorage $payloadStorage;

    protected OrmDialect $ormDialect;

    protected RouteInfo $routeInfo;

    public function setPayloadStorage(PayloadStorage $payloadStorage): void
    {

        $this->payloadStorage = $payloadStorage;
    }

    public function setRouteInfo(RouteInfo $routeInfo): void
    {

        $this->routeInfo = $routeInfo;
    }

    public function setOrmDialect(OrmDialect $ormDialect): void
    {

        $this->ormDialect = $ormDialect;
    }

    protected function onlyFields(): array
    {

        return ["id", "name"];
    }

    /**
     * @return a query builder after interacting with [payloadStorage]
    */
    abstract protected function getBaseCriteria(): object;

    /**
     * This is the only method caller cares about
    */
    final public function getBuilder(): object
    {

        return $this->ormDialect->selectFields(
            $this->getBaseCriteria(),
            $this->onlyFields()
        );
    }
}
