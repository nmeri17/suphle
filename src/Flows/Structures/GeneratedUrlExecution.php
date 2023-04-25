<?php

namespace Suphle\Flows\Structures;

use Suphle\Contracts\Presentation\BaseRenderer;

class GeneratedUrlExecution
{
    public function __construct(protected string $requestPath, protected readonly BaseRenderer $renderer)
    {

        //
    }

    public function changeUrl(string $newPath): void
    {

        $this->requestPath = $newPath;
    }

    public function getRenderer(): BaseRenderer
    {

        return $this->renderer;
    }

    public function getRequestPath(): string
    {

        return $this->requestPath;
    }
}
