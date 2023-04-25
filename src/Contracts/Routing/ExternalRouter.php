<?php

namespace Suphle\Contracts\Routing;

use Suphle\Contracts\Presentation\BaseRenderer;

interface ExternalRouter
{
    public function canHandleRequest(): bool;

    public function convertToRenderer(): BaseRenderer;
}
