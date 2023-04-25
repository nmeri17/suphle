<?php

namespace Suphle\Exception\Diffusers;

use Suphle\Contracts\Exception\ExceptionHandler;

use Suphle\Contracts\Presentation\{HtmlParser, BaseRenderer};

use Suphle\Request\RequestDetails;

use Suphle\Response\ModifiesRendererTemplate;

use Suphle\Exception\{ComponentEntry, Explosives\UnauthorizedServiceAccess};

use Throwable;

class UnauthorizedDiffuser implements ExceptionHandler
{
    use ModifiesRendererTemplate;

    public const ERRORS_PRESENCE = "authorization_failure_message";

    protected string $newMarkupName = "authorization-failure";

    public function __construct(
        protected readonly ComponentEntry $componentEntry,
        protected readonly HtmlParser $htmlParser,
        protected readonly BaseRenderer $renderer
    ) {

        //
    }

    /**
     * @param {origin} UnauthorizedServiceAccess
    */
    public function setContextualData(Throwable $origin): void
    {

        //
    }

    public function prepareRendererData(): void
    {

        $this->setMarkupDetails();

        $this->renderer->setRawResponse([

            self::ERRORS_PRESENCE => "Unauthorized"
        ])->setHeaders(403, []);
    }

    public function getRenderer(): BaseRenderer
    {

        return $this->renderer;
    }
}
