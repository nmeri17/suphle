<?php

namespace Suphle\Services\DecoratorHandlers;

use Suphle\Contracts\{Services\CallInterceptors\SystemModelEdit, Database\OrmDialect, Config\DecoratorProxy};

use Suphle\Hydration\Structures\ObjectDetails;

use ProxyManager\Proxy\AccessInterceptorInterface;

use Throwable;

class SystemModelEditHandler extends BaseInjectionModifier
{
    public function __construct(
        protected readonly OrmDialect $ormDialect,
        protected readonly ErrorCatcherHandler $errorDecoratorHandler,
        DecoratorProxy $proxyConfig,
        ObjectDetails $objectMeta
    ) {

        parent::__construct($proxyConfig, $objectMeta);
    }

    /**
     * @param {concrete} SystemModelEdit
    */
    public function examineInstance(object $concrete, string $caller): object
    {

        return $this->getProxy($concrete);
    }

    public function getMethodHooks(): array
    {

        return [

            "updateModels" => $this->wrapUpdateModels(...)
        ];
    }

    public function wrapUpdateModels(
        AccessInterceptorInterface $proxy,
        SystemModelEdit $concrete,
        string $methodName,
        array $argumentList
    ) {

        try {

            return $this->ormDialect->runTransaction(
            	
            	fn () => $concrete->updateModels(...$argumentList),

            	$concrete->modelsToUpdate(...$argumentList)
            );
        } catch (Throwable $exception) {

            return $this->errorDecoratorHandler->attemptDiffuse(
                $exception,
                $proxy,
                $concrete,
                $methodName
            );
        }
    }
}
