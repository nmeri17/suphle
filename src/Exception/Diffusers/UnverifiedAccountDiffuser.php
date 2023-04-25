<?php

namespace Suphle\Exception\Diffusers;

use Suphle\Contracts\{Exception\ExceptionHandler, Presentation\BaseRenderer};

use Suphle\Request\RequestDetails;

use Suphle\Response\Format\{Json, Redirect};

use Throwable;

class UnverifiedAccountDiffuser implements ExceptionHandler
{
    protected string $verificationUrl;

    protected BaseRenderer $renderer;

    public function __construct(
        protected readonly RequestDetails $requestDetails
    ) {

        //
    }

    public function setContextualData(Throwable $origin): void
    {

        $this->verificationUrl = $origin->verificationUrl;
    }

    /**
    * {@inheritdoc}
    */
    public function prepareRendererData(): void
    {

        if ($this->requestDetails->isApiRoute()) {

            $this->renderer = $this->getTokenRenderer();
        } else {
            $this->renderer = $this->getSessionRenderer();
        }
    }

    public function getRenderer(): BaseRenderer
    {

        return $this->renderer;
    }

    protected function getTokenRenderer(): BaseRenderer
    {

        $renderer = new Json("");

        $renderer->setRawResponse([

            "message" => "User be verified. Visit ". $this->verificationUrl . " to begin"
        ]);

        $renderer->setHeaders(400, []);

        return $renderer;
    }

    protected function getSessionRenderer(): BaseRenderer
    {

        $verificationPath = $this->verificationUrl;

        $renderer = new Redirect("", function () use ($verificationPath) {

            return $verificationPath;
        });

        return $renderer;
    }
}
