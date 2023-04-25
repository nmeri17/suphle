<?php

namespace Suphle\Routing\Decorators;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class HandlingCoordinator
{
    public function __construct(public readonly string $coordinatorName)
    {

        //
    }
}
