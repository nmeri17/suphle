<?php

namespace Suphle\Exception\Diffusers;

use Suphle\Contracts\Exception\{ExceptionHandler, BroadcastableException};

use Suphle\Contracts\Presentation\BaseRenderer;

use Suphle\Hydration\Container;

use Suphle\Request\RequestDetails;

use Suphle\Response\Format\{ Markup, Json};

use Suphle\Exception\DetectedExceptionManager;

use Suphle\Exception\Explosives\DevError\InvalidImplementor;

use Throwable;

class GenericDiffuser implements ExceptionHandler
{

    protected Throwable $origin;

    protected string $newMarkupName = "default";

    public function __construct(
        protected readonly RequestDetails $requestDetails,
        protected readonly DetectedExceptionManager $exceptionDetector,
        protected readonly Container $container,
    ) {

        //
    }

    public function setContextualData(Throwable $origin): void
    {

        $this->origin = $origin;
    }

    public function prepareRendererData(): void
    {

        if ($this->origin instanceof BroadcastableException) {

            $this->exceptionDetector->queueAlertAdapter(
                $this->origin,
                $this->requestDetails->getPath()
            );
        }

        if ($this->requestDetails->isApiRoute()) {

            $this->renderer = new Json([]);
        } else {
            $this->renderer = new Markup($this->newMarkupName, []);
        }

        $this->renderer->setRawResponse([

            "exception" => $this->origin
        ]);

        $incomingCode = $this->origin->getCode();

        $this->renderer->setHeaders(
            (is_int($incomingCode) && $incomingCode > 100) ?

            $incomingCode : 500,
            []
        );
    }

    public function getRenderer(): BaseRenderer
    {

        return $this->renderer;
    }
}
