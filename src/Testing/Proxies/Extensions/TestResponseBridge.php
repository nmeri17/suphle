<?php
namespace Suphle\Testing\Proxies\Extensions;

use Suphle\Contracts\{IO\Session as SessionContract, Presentation\BaseRenderer};

use Illuminate\{Testing\TestResponse, Http\Response};

class TestResponseBridge extends TestResponse
{
    public function __construct(
        Response $response,
        protected readonly SessionContract $sessionClient,
        public readonly BaseRenderer $lastRenderer
    ) {
        parent::__construct($response);
    }

    public function session()
    {

        return $this->sessionClient;
    }
}
