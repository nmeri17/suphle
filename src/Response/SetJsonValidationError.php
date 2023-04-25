<?php

namespace Suphle\Response;

use Suphle\Contracts\Presentation\BaseRenderer;

use Suphle\Request\RequestDetails;

use Suphle\Response\Format\Json;

trait SetJsonValidationError
{
    public function shouldSetCode(RequestDetails $requestDetails, BaseRenderer $renderer): bool
    {

        return $requestDetails->isApiRoute() || $renderer instanceof Json;
    }
}
