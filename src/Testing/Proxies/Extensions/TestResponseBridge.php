<?php

namespace Suphle\Testing\Proxies\Extensions;

use Suphle\Contracts\IO\Session as SessionContract;

use Illuminate\{Testing\TestResponse, Http\Response};

class TestResponseBridge extends TestResponse
{
    public function __construct(Response $response, protected readonly SessionContract $sessionClient)
    {

        parent::__construct($response);
    }

    protected function session()
    {

        return $this->sessionClient;
    }
}
