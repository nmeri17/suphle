<?php

namespace Suphle\Services\Decorators;

use Suphle\Contracts\Services\CallInterceptors\ServiceErrorCatcher;

use Attribute;

/**
 * this is the attr that actually activates proxifying. it needs a target name to know what handler should wrap the class
 * @throws Exception if target doesn't implement {interceptType}
*/
#[Attribute(Attribute::TARGET_CLASS)]
class InterceptsCalls
{
    public function __construct(
        public readonly string $interceptType = ServiceErrorCatcher::class
    ) {

        //
    }
}
