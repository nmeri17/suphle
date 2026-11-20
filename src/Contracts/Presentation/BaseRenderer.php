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
}
