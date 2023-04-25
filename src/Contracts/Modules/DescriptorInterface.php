<?php

namespace Suphle\Contracts\Modules;

use Suphle\Hydration\Container;

interface DescriptorInterface
{
    /**
     * Interface which will be consumers' API on this module
    */
    public function exportsImplements(): string;

    public function getContainer(): Container;

    public function globalConcretes(): array;
}
