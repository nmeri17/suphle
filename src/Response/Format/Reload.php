<?php

namespace Suphle\Response\Format;

use Suphle\Services\Decorators\VariableDependencies;

use Suphle\Request\PayloadStorage;

use Suphle\Contracts\Response\RendererManager;

#[VariableDependencies([ "setRendererManager" ])]
class Reload extends GenericRenderer
{
    public const STATUS_CODE = 200;

    protected RendererManager $rendererManager;

    protected int $statusCode = self::STATUS_CODE;

    public function render(): string
    {
        return $this->rendererManager
            ->invokePreviousRenderer()
            ->render();
    }
}
