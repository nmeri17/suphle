<?php

namespace Suphle\Contracts\Modules;

use Suphle\Contracts\Presentation\BaseRenderer;

interface HighLevelRequestHandler
{
    public function handlingRenderer(): ?BaseRenderer;
}
