<?php

namespace Suphle\Response\Format;

use Suphle\Services\Decorators\VariableDependencies;

use Suphle\Request\PayloadStorage;

use Suphle\Contracts\Response\RendererManager;
use Suphle\Contracts\Presentation\MirrorableRenderer;
use Suphle\Contracts\Response\OpenApiRenderer;
use Suphle\Response\Traits\OpenApiRendererTrait;

#[VariableDependencies([ "setRendererManager" ])]
class Reload extends GenericRenderer implements MirrorableRenderer, OpenApiRenderer
{
    use OpenApiRendererTrait;

    public const STATUS_CODE = 200;

    protected RendererManager $rendererManager;

    public function __construct()
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
            ->invokePreviousRenderer()
            ->render();
    }

    /**
     * Override default status code for Reload
     */
    public static function getStatusCode(): int
    {
        return self::STATUS_CODE;
    }

    /**
     * Override default response schema for Reload
     */
    public static function getResponseSchema(): array
    {
        return [
            'type' => 'string',
            'description' => static::getDescription()
        ];
    }

    /**
     * Override default description for Reload
     */
    public static function getDescription(): string
    {
        return 'Page reload response';
    }
}
