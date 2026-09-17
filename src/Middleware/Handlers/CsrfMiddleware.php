<?php

namespace Suphle\Middleware\Handlers;

use Suphle\Middleware\{MiddlewareNexts, BaseMiddleware};

use Suphle\Request\{PayloadStorage, RequestDetails};

use Suphle\Contracts\{Auth\AuthStorage, Presentation\BaseRenderer};

use Suphle\Auth\Storage\SessionStorage;

use Suphle\Exception\Explosives\DevError\CsrfException;

use Suphle\Security\CSRF\CsrfGenerator;

class CsrfMiddleware extends BaseMiddleware
{
    public function __construct(
        protected readonly CsrfGenerator $generator, 
        protected readonly RequestDetails $requestDetails, 
        protected readonly AuthStorage $authStorage
    ) {}

    public function process(PayloadStorage $payloadStorage, ?MiddlewareNexts $requestHandler): BaseRenderer
    {
        if (
            $this->requestDetails->isGetRequest() ||

            !$this->requestDetails->isApiRoute()
        ) {

            return $requestHandler->handle($payloadStorage);
        }

        if (!$this->generator->isVerifiedToken(
            $payloadStorage->getKey(CsrfGenerator::TOKEN_FIELD)
        )) {

            throw new CsrfException();
        }

        return $requestHandler->handle($payloadStorage);
    }
}