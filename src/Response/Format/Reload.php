<?php

namespace Suphle\Response\Format;

use Suphle\Services\Decorators\VariableDependencies;

use Suphle\Request\PayloadStorage;

use Suphle\Contracts\Response\RendererManager;

#[VariableDependencies([ "setRendererManager" ])]
class Reload extends GenericRenderer
{
    public const STATUS_CODE = 302;

    protected RendererManager $rendererManager;

    public function __construct(

        public iterable $rawResponse = []
    ) {
        $this->setHeaders(self::STATUS_CODE, [
            PayloadStorage::LOCATION_KEY => "" // will later pick previous url
        ]);
    }

    public function render(): string
    {
        return $this->rendererManager
            ->rendererDataOnError($this->rawResponse)
            ->render();
    }

    public function setRendererManager(RendererManager $rendererManager): void
    {
        $this->rendererManager = $rendererManager;
    }
}
