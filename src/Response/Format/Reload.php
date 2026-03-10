<?php

namespace Suphle\Response\Format;

use Suphle\Services\Decorators\VariableDependencies;

use Suphle\Request\PayloadStorage;

use Suphle\Contracts\Response\RendererManager;
use Suphle\Contracts\Response\OpenApiRenderer;
use Suphle\Response\Traits\OpenApiRendererTrait;

#[VariableDependencies([ "setRendererManager" ])]
class Reload extends GenericRenderer implements OpenApiRenderer
{
    use OpenApiRendererTrait;

    public const STATUS_CODE = 200;

    protected RendererManager $rendererManager;

    protected int $statusCode = self::STATUS_CODE;

    public function render(): string
    {
        return $this->rendererManager
            ->invokePreviousRenderer()
            ->render();
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
