<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\OutgoingRequests;

use Suphle\IO\Http\BaseHttpRequest;

use Psr\Http\Message\ResponseInterface;

class VisitSegment extends BaseHttpRequest
{
    public function getRequestUrl(): string
    {

        return "http://localhost:8080/segment"; // baseAddress must match what's in rr.yaml
    }

    protected function getHttpResponse(): ResponseInterface
    {

        return $this->requestClient->request(
            "get",
            $this->getRequestUrl()/*, $options*/
        );
    }

    protected function convertToDomainObject(ResponseInterface $response)
    {

        return $response;
    }
}
