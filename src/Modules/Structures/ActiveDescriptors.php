<?php

namespace Suphle\Modules\Structures;

use Suphle\Hydration\Container;

use Suphle\Contracts\Modules\DescriptorInterface;

/**
 * Using this to avoid pulling ModuleHandlerIdentifier into lower level classes just to access app modules
*/
class ActiveDescriptors
{
    public function __construct(protected readonly array $originalDescriptors)
    {

        //
    }

    public function firstOriginalContainer(): Container
    {

        return current($this->originalDescriptors)->getContainer();
    }

    public function getOriginalDescriptors(): array
    {

        return $this->originalDescriptors;
    }

    public function findMatchingExports(string $moduleInterface): ?DescriptorInterface
    {

        foreach ($this->originalDescriptors as $descriptor) {

            if ($moduleInterface == $descriptor->exportsImplements()) {

                return $descriptor;
            }
        }

        return null;
    }
}
