<?php
namespace Suphle\Middleware;

use Suphle\Hydration\Container;
use Suphle\Request\PayloadStorage;
use Suphle\Contracts\Config\Router as RouterConfig;
use Suphle\Contracts\Presentation\BaseRenderer;

class MiddlewareQueue
{
    protected array $mergedStack = [];

    public function __construct(
        protected readonly Container $container,
        protected readonly RouterConfig $routerConfig,
        protected readonly PayloadStorage $payloadStorage,
        protected readonly array $boundPreMidw,
        protected readonly array $boundMidw
    ) {}

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
            $this->hydrateMap($this->boundPreMidw ),
            $this->hydrateMap($this->boundMidw ),
            array_map(
                fn () => $this->container->getClass(...),

                $this->routerConfig->defaultMiddleware()
            )
        );
    }

    protected function hydrateMap (array $midwList):array {

        $hydrated = [];
        foreach ($middlewareMap as $handlerClass => $args) {
            
            $concrete = $this->container->getClass($handlerClass);

            $concrete->setUserArgs($args);

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
