<?php

namespace Suphle\Services\DecoratorHandlers;

use Suphle\Contracts\Services\CallInterceptors\{SystemModelEdit, MultiUserModelEdit};

use Suphle\Services\Decorators\DomainService;

use Suphle\Hydration\Structures\ObjectDetails;

use Suphle\Request\RequestDetails;

use Suphle\Exception\Explosives\DevError\MissingPostDecorator;

class SecuresPostRequestHandler extends BaseArgumentModifier
{
    protected array $postDecorators = [

        SystemModelEdit::class, MultiUserModelEdit::class
    ];

    public function __construct(
        protected readonly RequestDetails $requestDetails,
        ObjectDetails $objectMeta
    ) {

        parent::__construct($objectMeta);
    }

    public function transformConstructor(object $dummyInstance, array $arguments): array
    {

        if (!$this->requestDetails->matchesMethod("put")) {

            return $arguments;
        }

        foreach ($arguments as $dependency) {

            if (!is_object($dependency)) continue;

            $className = $dependency::class;

            $domainAttrs = $this->objectMeta->getClassAttributes($className, DomainService::class);

            if (empty($domainAttrs)) continue;

            $domainService = $domainAttrs[0]->newInstance();

            // 2. If marked for mutation, verify it also has the Interceptor (via Interface check)
            if ($domainService->mutation === true) {

                foreach ($this->postDecorators as $decorator) {

                    if ($this->objectMeta->implementsInterface(
                        $dependency::class,
                        $decorator
                    )) {

                        return $arguments;
                    }
                }
            }
        }

        throw new MissingPostDecorator($dummyInstance::class);
    }
}
