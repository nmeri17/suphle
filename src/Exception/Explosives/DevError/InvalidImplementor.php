<?php

namespace Suphle\Exception\Explosives\DevError;

use Suphle\Contracts\Exception\BroadcastableException;

use Exception;

class InvalidImplementor extends Exception implements BroadcastableException
{
    public static function incompatibleParent(string $interface, string $concrete): self
    {

        return new self($concrete ." incorrectly bound to ". $interface);
    }

    public static function missingParent(string $interface, string $caller, array $callStack): self
    {

        return new self(
            "Unable to hydrate '$caller' because no concrete class was bound to '$interface'. The following hydration sequence warranted this interface:\n".

            var_export(array_values(
                array_unique($callStack)
            ), true)
        );
    }
}
