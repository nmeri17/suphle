<?php

namespace Suphle\Hydration\Structures;

use Closure;

class NamespaceUnit
{
    public function __construct(
        protected readonly string $fromNamespace,
        protected readonly string $newLocation,

        /**
           * @param {nameResolver} Function(string $requestedInterface):string
        *	[requestedInterface] has the namespace trimmed away. It's the entity requested at runtime
        */
        protected readonly Closure $nameResolver
    ) {

        //
    }

    public function getSource(): string
    {

        return $this->fromNamespace;
    }

    public function getNewName(string $incomingEntity): string
    {

        return call_user_func($this->nameResolver, $incomingEntity);
    }

    public function getLocation(): string
    {

        return $this->newLocation;
    }
}
