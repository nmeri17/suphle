<?php
namespace Suphle\Middleware;

use Suphle\Hydration\Container;
use Suphle\Request\PayloadStorage;

use Suphle\Contracts\{Presentation\BaseRenderer, Routing\MiddlewareRegistry, Config\Router as RouterConfig};

class BaseMiddlewareRegistry implements MiddlewareRegistry
{
    protected array $mergedStack = [],

    $preMiddleware = [], $middleware = [];

    public function __construct(
        protected readonly Container $container,
        protected readonly RouterConfig $routerConfig,
        protected readonly PayloadStorage $payloadStorage,
    ) {}

    public function setRawHandlers (array $preMiddleware, array $middleware):self {

        $this->preMiddleware = $preMiddleware;

        $this->middleware = $middleware;
    }

    public function runStack(): BaseRenderer
    {
        if (empty($this->mergedStack)) {
            $this->setMergedStack();
        }

        $stack = $this->mergedStack;
        $outermost = array_shift($stack);

        return $outermost->process(
            $this->payloadStorage,
            $this->getHandlerChain($stack)
        );
    }

    protected function setMergedStack(): void {

        $this->mergedStack = array_merge(
            $this->hydrateMap($this->preMiddleware ),
            $this->hydrateMap($this->middleware ), // offers dev opportunity to override regular request handling
            $this->hydrateMap($this->routerConfig->defaultMiddleware())
        );
    }

    protected function hydrateMap (array $middlewareList):array {

        $hydrated = [];
        foreach ($middlewareList as $handlerClass => $args) {
            
            $concrete = $this->container->getClass($handlerClass);

            $concrete->setArgs($args);

            $hydrated[] = $concrete;
        }
        return $hydrated;
    }

    /**
     *  convert each middleware to a request interface carrying the next one so triggering each one creates a chain effect till the last one
     * @param {accumNexts} null for the final handler since there's none below it
     * @return null for the last handler in the chain
    */
    private function getHandlerChain(array $middlewareList, MiddlewareNexts $accumNexts = null): ?MiddlewareNexts
    {

        if (empty($middlewareList)) {
            return $accumNexts;
        }

        $lastMiddleware = array_pop($middlewareList); // we're reading from behind so that last item on the list is what is passed to the caller, and thus, is first to be evaluated on our way down the rabbit hole

        $nextHandler = new MiddlewareNexts($lastMiddleware, $accumNexts);

        // [1,2,4] => [4(2(1(cur, null), cur), cur)]
        /* [1,2,4] => 1,[2,4]
        [2,4] => 2,[4]
        [4] = each level injests its predecessor
        */
        return $this->getHandlerChain($middlewareList, $nextHandler);
    }
}
