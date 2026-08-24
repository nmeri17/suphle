<?php
namespace Suphle\Testing\Proxies\Extensions;

use Suphle\Middleware\BaseMiddlewareRegistry;

class MiddlewareManipulator extends BaseMiddlewareRegistry
{
    protected bool $stackAlwaysEmpty = false;

    protected array $preExclude = [], $preInclude = [];

    /**
     * these must be called before an routing takes place. handlers given here will be processed before those on the actual route
     *
     * @param {handlers} middlewares[]
    */
    public function addToActiveStack(array $handlers): void
    {

        $this->preInclude = $handlers;
    }

    /**
     * @param {handlerNames} middlewares::class[]
    */
    public function disableCollectors(array $handlerNames): void
    {

        $this->preExclude = $handlerNames;
    }

    public function disableAll(): void
    {

        $this->stackAlwaysEmpty = true;
    }

    protected function setMergedStack(): void {

        if ($this->stackAlwaysEmpty) {
            $this->mergedStack = [];

            return;
        }

        $stack = $this->hydrateMap($this->preInclude);

        parent::setMergedStack();

        $parentStack = $this->mergedStack;

        foreach ($parentStack as $index => $handler) {

            if (in_array($handler::class, $this->preExclude)) {

                unset($parentStack[$index]);
            }
        }
        $this->mergedStack = [...$stack, ...$parentStack];
    }

    public function getNotUsed(array $toFilter): array {

        $allHandlers = array_map(
            $this->mergedStack, fn ($handler) => $handler::class
        );

        return array_filter($toFilter, fn ($handler) => !in_array($handler, $allHandlers));
    }
}
