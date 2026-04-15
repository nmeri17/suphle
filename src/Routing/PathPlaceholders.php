<?php

namespace Suphle\Routing;

use Suphle\Request\SanitizesIntegerInput;

use Suphle\Services\Decorators\BindsAsSingleton;

/**
 * Used by route finder during matching to compose and interpolate patterns read from collections and what is incoming in request
*/
#[BindsAsSingleton]
class PathPlaceholders
{
    use SanitizesIntegerInput;

    protected array $stack = [];
    protected array $methodSegments = [];

    protected bool $hasExchangedTokens = false;

    public function getSegmentValue(string $name)
    {

        return $this->stack[$name];
    }

    public function getAllSegmentValues(): array
    {

        return $this->stack;
    }

    /**
     * Should be called before the readers start calling [getSegmentValue]
    */
    public function allNumericToPositive(): void
    {

        $this->stack = $this->allInputToPositive($this->stack);
    }

    public function getKeyForPositiveInt(string $key): int
    {

        return $this->positiveIntValue($this->stack[$key]);
    }

    public function setSegmentValues(array $values): void
    {
        $this->stack = $values;
    }

    public function clearAllSegments(): void
    {

        $this->stack = [];

        $this->hasExchangedTokens = false; // since this object may be long-lived, without this, the placeholder stack won't be re-computed
    }
}
