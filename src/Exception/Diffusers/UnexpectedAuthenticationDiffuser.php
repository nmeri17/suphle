<?php

namespace Suphle\Exception\Diffusers;

use Suphle\Contracts\Exception\ExceptionHandler;

use Suphle\Contracts\Presentation\BaseRenderer;

use Suphle\Response\Format\Json;

use Suphle\Exception\Explosives\UnexpectedAuthentication;

use Throwable;

class UnexpectedAuthenticationDiffuser implements ExceptionHandler
{
    protected UnexpectedAuthentication $exception;

    protected BaseRenderer $renderer;

    public function setContextualData(Throwable $origin): void
    {
        $this->exception = $origin;
    }

    public function prepareRendererData(): void
    {
        $this->renderer = new Json([
            'error' => 'UnexpectedAuthentication',
            'message' => 'This route is only accessible to guest users. You are already authenticated.'
        ]);

        $this->renderer->setHeaders(403, []);
    }

    public function getRenderer(): BaseRenderer
    {
        return $this->renderer;
    }
} 