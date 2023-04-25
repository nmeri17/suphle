<?php

namespace Suphle\Contracts\Response;

use Suphle\Contracts\Presentation\BaseRenderer;

interface BaseResponseManager
{
    public function responseRenderer(): BaseRenderer;

    public function afterRender($data = null): void;
}
