<?php

namespace Suphle\Contracts\Requests;

use Suphle\Contracts\Presentation\BaseRenderer;

use Suphle\Request\RequestDetails;

interface ValidationEvaluator
{
    public function getValidatorErrors(): array;

    public function validationRenderer(array $failureDetails): BaseRenderer;

    public function shouldSetCode(RequestDetails $requestDetails, BaseRenderer $renderer): bool;
}
