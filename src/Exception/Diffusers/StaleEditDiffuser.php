<?php

namespace Suphle\Exception\Diffusers;

use Suphle\Contracts\{Exception\ExceptionHandler, Presentation\BaseRenderer, Response\RendererManager};

use Suphle\Routing\RouteManager;

use Suphle\Request\RequestDetails;

use Suphle\Exception\Explosives\EditIntegrityException;

use Throwable;

class StaleEditDiffuser implements ExceptionHandler
{
    private Throwable $renderer;

    public function __construct(
        protected readonly RendererManager $rendererManager
    ) {

        //
    }

    /**
     * @param {origin} EditIntegrityException
    */
    public function setContextualData(Throwable $origin): void
    {

        //
    }

    public function prepareRendererData(): void
    {

        $this->renderer = $this->rendererManager

        ->invokePreviousRenderer([

            "errors" => [["message" => "Another user recently updated this resource"]]
        ])
        ->setHeaders(400, []);
    }

    public function getRenderer(): BaseRenderer
    {

        return $this->renderer;
    }
}
