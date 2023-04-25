<?php

namespace Suphle\Services;

abstract class IndicatesCaughtException
{
    protected $didHaveErrors = false;

    public function hasErrors(): bool
    {

        return $this->didHaveErrors;
    }

    /**
     * To be called from [getDomainObject]
    */
    protected function translationFailure(): void
    {

        $this->didHaveErrors = true;
    }

    /**
     * This is a high-level method for use by direct chldren of this class. Those classes, Children, ought to define their own `convertToDomainObject` method conforming to the context of that child
     * @return Nullable
    */
    abstract public function getDomainObject();
}
