<?php

namespace Suphle\Contracts\Presentation;

use Suphle\Flows\ControllerFlows;

use Suphle\Services\BaseCoordinator;

/**
 * Psr\Http\Message\ResponseInterface, if you will
*/
interface BaseRenderer
{
    public function render(): string;

    public function setHeaders(int $statusCode, array $headers): void;

    public function setRawResponse(iterable $response): self;

    public function getRawResponse(): iterable;

    public function getStatusCode(): int;

    public function getHeaders(): array;

    /**
     * Determines whether this renderer is fit for writing validation errors to directly or whether it should be deferred to the renderer of the preceding request
    */
    public function deferValidationContent(): bool;

    public function forceArrayShape(array $includeData = []): void;
}
