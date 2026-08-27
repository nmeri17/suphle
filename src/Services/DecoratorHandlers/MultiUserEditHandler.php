<?php

namespace Suphle\Services\DecoratorHandlers;

use Suphle\Contracts\{Services\CallInterceptors\MultiUserModelEdit, Database\OrmDialect, Config\DecoratorProxy, Auth\AuthStorage, Routing\Middleware};

use Suphle\Request\PayloadStorage;

use Suphle\Routing\Structures\RouteInfo;

use Suphle\Auth\Middleware\PathAuthorization;

use Suphle\Hydration\Structures\ObjectDetails;

use Suphle\Exception\Explosives\EditIntegrityException;

use ProxyManager\Proxy\AccessInterceptorInterface;

use Throwable;
use DateTime;

/**
 * The idea is that the last updater should invalidate whatever those with current copies of the page are both looking at or trying to update
*/

class MultiUserEditHandler extends BaseInjectionModifier
{
    final public const INTEGRITY_KEY = "_collision_protect"; // submitted form/payload is expected to contain this key

    final public const DATE_FORMAT = "Y-m-d H:i:s";

    public function __construct(
        protected readonly OrmDialect $ormDialect,
        protected readonly PayloadStorage $payloadStorage,

        /**
         * I'm composing instead of extending to decouple constructor dependencies
        */
        protected readonly ErrorCatcherHandler $errorDecoratorHandler,
        DecoratorProxy $proxyConfig,
        ObjectDetails $objectMeta,
        protected readonly RouteInfo $routeInfo,
        protected readonly AuthStorage $authStorage
    ) {

        parent::__construct($proxyConfig, $objectMeta);
    }

    /**
     * @param {concrete} MultiUserModelEdit
    */
    public function examineInstance(object $concrete, string $caller): object
    {

        return $this->getProxy($concrete);
    }

    public function getMethodHooks(): array
    {

        return [

            "updateResource" => $this->wrapUpdateResource(...),

            "getResource" => $this->wrapGetResource(...)
        ];
    }

    /**
     * @return mixed. Operation result
     *
     * @throws EditIntegrityException
    */
    public function wrapUpdateResource(
        AccessInterceptorInterface $proxy,
        MultiUserModelEdit $concrete,
        string $methodName,
        array $argumentList
    ) {

        if (!$this->payloadStorage->hasKey(self::INTEGRITY_KEY)) {

            throw new EditIntegrityException(EditIntegrityException::MISSING_KEY);
        }

        $currentVersion = $concrete->getResource(clone $argumentList["builder"]);

        $integrityValue = $this->payloadStorage->getKey(self::INTEGRITY_KEY);

        if (!$currentVersion->includesEditIntegrity($integrityValue)) { // this is the heart of the entire decoration

            throw new EditIntegrityException(EditIntegrityException::KEY_MISMATCH);
        }

        try {

            return $this->ormDialect->runTransaction(function () use ($currentVersion, $concrete, $integrityValue, $argumentList) {

                $result = $concrete->updateResource(...$argumentList); // user's incoming changes

                $currentVersion->nullifyEditIntegrity(
                    new DateTime($integrityValue)
                );

                if ($currentVersion->enableAudit()) {

                    $currentVersion->makeHistory(
                        $this->authStorage,
                        $this->payloadStorage->fullPayload()
                    );
                }

                return $result;

            }, [$currentVersion], true);
        } catch (Throwable $exception) {

            return $this->errorDecoratorHandler->attemptDiffuse(
                $exception,
                $proxy,
                $concrete,
                $methodName
            );
        }
    }

    public function wrapGetResource(
        AccessInterceptorInterface $proxy,
        MultiUserModelEdit $concrete,
        string $methodName,
        array $argumentList
    ) {

        if (
            in_array(PathAuthorization::class, $this->routeInfo->preMiddlewares) ||
            $this->hasParentAuth()
        )
            throw new EditIntegrityException(EditIntegrityException::NO_AUTHORIZER);

        return $concrete->getResource(...$argumentList); // we're not wrapping in error catcher since we want request termination if getting editable resource failed; there's nothing to fallback to
    }
    protected function hasParentAuth ():bool {

        $matchTag = array_filter($this->routeInfo->preMiddlewares, function (string $middleware) {

            return $this->objectMeta->stringInClassTree($middleware, PathAuthorization::class);
        });

        return !empty($matchTag);
    }
}
