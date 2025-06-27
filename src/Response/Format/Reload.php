<?php

namespace Suphle\Response\Format;

use Suphle\Services\Decorators\VariableDependencies;

use Suphle\Request\PayloadStorage;

use Suphle\Contracts\Response\RendererManager;

#[VariableDependencies([ "setRendererManager" ])]
class Reload extends BaseHtmlRenderer
{
    public const STATUS_CODE = 205; // Reset Content

    protected RendererManager $rendererManager;

    public function __construct(protected iterable $data = [])
    {
        $this->setHeaders(self::STATUS_CODE, [
            PayloadStorage::CONTENT_TYPE_KEY => PayloadStorage::HTML_HEADER_VALUE
        ]);
    }

    public function setRendererManager(RendererManager $rendererManager): void
    {
        $this->rendererManager = $rendererManager;
    }

    public function render(): string
    {
        return $this->rendererManager
            ->invokePreviousRenderer($this->data)
            ->render();
    }
}
