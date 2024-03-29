<?php

namespace Suphle\Response\Format;

use Suphle\Contracts\Presentation\{HtmlParser, BaseRenderer};

use Suphle\Services\Decorators\VariableDependencies;

use Suphle\Hydration\Container;

use Suphle\Flows\ControllerFlows;

use Suphle\Services\ServiceCoordinator;

abstract class GenericRenderer implements BaseRenderer
{
    protected ServiceCoordinator $coordinator;

    protected ?ControllerFlows $flows = null;

    protected string $routeMethod;

    protected string $handler;

    protected int $statusCode;

    protected array $headers = [];

    protected iterable $rawResponse = [];

    protected bool $shouldDeferValidationFailure = true;

    public function setCoordinatorClass(ServiceCoordinator $coordinator): void
    {

        $this->coordinator = $coordinator;
    }

    public function invokeActionHandler(array $handlerParameters): BaseRenderer
    {

        $this->rawResponse = call_user_func_array(
            [$this->getCoordinator(), $this->handler],
            $handlerParameters
        );

        return $this;
    }

    public function getCoordinator(): ServiceCoordinator
    {

        return $this->coordinator;
    }

    protected function renderJson(): string
    {

        return json_encode($this->rawResponse, JSON_THROW_ON_ERROR);
    }

    public function hasBranches(): bool
    {

        return !is_null($this->getFlow());
    }

    public function setRawResponse(iterable $response): BaseRenderer
    {

        $this->rawResponse = $response;

        return $this;
    }

    public function setFlow(ControllerFlows $flow): BaseRenderer
    {

        $this->flows = $flow;

        return $this;
    }

    public function getFlow(): ?ControllerFlows
    {

        return $this->flows;
    }

    public function getRawResponse(): iterable
    {

        return $this->rawResponse;
    }

    public function getRouteMethod(): string
    {

        return $this->routeMethod;
    }

    public function setRouteMethod(string $httpMethod): void
    {

        $this->routeMethod = $httpMethod;
    }

    public function getHandler(): string
    {

        return $this->handler;
    }

    public function matchesHandler(string $name): bool
    {

        return $this->handler == $name;
    }

    public function setHeaders(int $statusCode, array $headers): void
    {

        $this->statusCode = $statusCode;

        $this->headers = array_merge($this->headers, $headers);
    }

    public function getStatusCode(): int
    {

        return $this->statusCode;
    }

    public function getHeaders(): array
    {

        return $this->headers;
    }

    public function deferValidationContent(): bool
    {

        return $this->shouldDeferValidationFailure;
    }

    /**
    * Insurance against routes that can possibly fail validation that don't return an array
    */
    public function forceArrayShape(array $includeData = []): void
    {

        $currentBody = $this->rawResponse;

        if (!is_array($currentBody)) {

            $currentBody = json_decode(
                json_encode($currentBody, JSON_THROW_ON_ERROR),
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        }

        $this->rawResponse = array_merge($currentBody, $includeData);
    }
}
