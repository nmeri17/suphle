<?php

namespace Suphle\Contracts\Response;

use Suphle\Request\PayloadStorage;

use Suphle\Contracts\Presentation\BaseRenderer;

use Suphle\Exception\Explosives\{ValidationFailure, Generic\NoCompatibleValidator};

interface RendererManager
{
    public function bootDefaultRenderer(): self;

    public function handleValidRequest(PayloadStorage $payloadStorage): BaseRenderer;

    public function fetchHandlerParameters(
        string $coodinator,
        string $handlingMethod
    ): array;

    /**
     * @throws ValidationFailure
    */
    public function mayBeInvalid(?BaseRenderer $renderer = null): self;

    public function invokePreviousRenderer(array $toMerge = []): BaseRenderer;

    /**
     * @throws NoCompatibleValidator
    */
    public function acquireValidatorStatus(string $coodinator, string $handlingMethod): bool;
}
