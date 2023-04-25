<?php

namespace Suphle\Contracts\Requests;

use Suphle\Contracts\Presentation\BaseRenderer;

use Suphle\Adapters\Presentation\Hotwire\Formats\BaseHotwireStream;

interface ValidationFailureConvention
{
    public function deriveFormPartial(BaseHotwireStream $renderer, array $failureDetails): BaseRenderer;
}
