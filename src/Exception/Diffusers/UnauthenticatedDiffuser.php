<?php

namespace Suphle\Exception\Diffusers;

use Suphle\Contracts\{Exception\ExceptionHandler, Config\AuthConfig, Presentation\BaseRenderer};

use Suphle\Request\RequestDetails;

use Suphle\Response\Format\{ Redirect, Json};

use Suphle\Request\PayloadStorage;

use Suphle\Exception\Explosives\Unauthenticated;

use Suphle\Auth\Storage\TokenStorage;

use Throwable;

class UnauthenticatedDiffuser implements ExceptionHandler
{
    public const ERRORS_PRESENCE = "message";
    public const RAW_RESPONSE = [

        self::ERRORS_PRESENCE => "Unauthenticated"
    ];
    private $renderer;

    /**
     * @param {origin} Unauthenticated
    */
    public function setContextualData(Throwable $origin): void
    {

        if ($origin->storageMechanism() instanceof TokenStorage) {

            $this->renderer = $this->getTokenRenderer();
        } else {
            $this->renderer = $this->getSessionRenderer();
        }
    }

    public function prepareRendererData(): void
    {

        $this->renderer->setHeaders(401, []);
    }

    public function getRenderer(): BaseRenderer
    {

        return $this->renderer;
    }

    protected function getTokenRenderer(): BaseRenderer
    {

        return new Json(static::RAW_RESPONSE);
    }

    protected function getSessionRenderer(): BaseRenderer
    {

        return new Redirect(function (
            RequestDetails $requestDetails,
            AuthConfig $authContract,
            PayloadStorage $payloadStorage
        ) {
            return $authContract->markupRedirect() . "?". http_build_query([

                "path" => $requestDetails->getPath(),

                "query" => $payloadStorage->fullPayload()
            ]);
        });
    }
}
