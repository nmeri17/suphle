<?php

namespace Suphle\Exception\Diffusers;

use Suphle\Contracts\{Exception\ExceptionHandler, Presentation\BaseRenderer, Response\RendererManager};

use Suphle\Response\Format\{Json, Markup};

use Suphle\Exception\{ComponentEntry, Explosives\UnauthorizedServiceAccess};

use Throwable;

class UnauthorizedDiffuser implements ExceptionHandler
{

    public const ERRORS_PRESENCE = "authorization_failure_message";

    public const RAW_RESPONSE = [

        self::ERRORS_PRESENCE => "Unauthorized"
    ];

    protected string $newMarkupName = "authorization-failure";

    protected BaseRenderer $renderer;

    public function __construct(
        protected readonly ComponentEntry $componentEntry,

        protected readonly RendererManager $rendererManager
    ) { }

    /**
     * @param {origin} UnauthorizedServiceAccess
    */
    public function setContextualData(Throwable $origin): void
    {

        //
    }

    /**
    * {@inheritdoc}
    */
    public function prepareRendererData(): void
    {

        if ($this->rendererManager->fallbackShouldJson()) {

            $this->renderer = new Json([]);
        } else {
            $this->renderer = new Markup($this->newMarkupName, []);
        }

        $this->renderer->setRawResponse(static::RAW_RESPONSE)->setHeaders(403, []);
    }

    public function getRenderer(): BaseRenderer
    {

        return $this->renderer;
    }
}
