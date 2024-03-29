<?php

namespace Suphle\Services\Structures;

use Suphle\Services\Decorators\VariableDependencies;

use Suphle\Request\PayloadStorage;

use Suphle\Routing\PathPlaceholders;

use Suphle\Services\IndicatesCaughtException;

use Throwable;

/**
 * Aside handling requests that don't map to models/entities, this is useful for things like callback endpoints where a user is waiting for feedback on our end, but obviously not on the automated, calling service's end. In such cases, mere validation errors won't cut it. We need to respond to the waiting services with something to complete user flow
*/
#[VariableDependencies([ "setPayloadStorage", "setPlaceholderStorage"])]
abstract class ModellessPayload extends IndicatesCaughtException
{
    protected PayloadStorage $payloadStorage;

    protected PathPlaceholders $pathPlaceholders;

    protected ?Throwable $exception = null;

    public function setPayloadStorage(PayloadStorage $payloadStorage): void
    {

        $this->payloadStorage = $payloadStorage;
    }

    public function setPlaceholderStorage(PathPlaceholders $pathPlaceholders): void
    {

        $this->pathPlaceholders = $pathPlaceholders;
    }

    /**
     * {@inheritdoc}
    */
    public function getDomainObject()
    {

        try {

            return $this->convertToDomainObject();
        } catch (Throwable $exception) {

            $this->exception = $exception;

            // throw $exception;
            return $this->translationFailure();
        }
    }

    /**
     * @throws Throwable, when it meets an unexpected/undesirable payload
    */
    abstract protected function convertToDomainObject();
}
