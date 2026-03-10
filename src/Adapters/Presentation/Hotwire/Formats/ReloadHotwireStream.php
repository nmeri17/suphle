<?php

namespace Suphle\Adapters\Presentation\Hotwire\Formats;

use Suphle\Contracts\Response\OpenApiRenderer;
use Suphle\Response\Traits\OpenApiRendererTrait;

use Suphle\Response\Format\Reload;

use Suphle\Contracts\Response\RendererManager;

use Suphle\Services\Decorators\VariableDependencies;

#[VariableDependencies([ "setRendererManager" ])]
class ReloadHotwireStream extends BaseHotwireStream implements OpenApiRenderer
{
    use OpenApiRendererTrait;

    public const STATUS_CODE = 303;

    protected int $statusCode = self::STATUS_CODE;

    public function __construct()
    {
        $this->fallbackRenderer = new Reload();
    }

    public function setRendererManager(RendererManager $rendererManager): void
    {
        $this->fallbackRenderer->setRendererManager($rendererManager);
    }

    /**
     * Override default response schema for ReloadHotwireStream
     */
    public static function getResponseSchema(): array
    {
        return [
            'type' => 'string',
            'format' => 'html',
            'description' => static::getDescription()
        ];
    }

    /**
     * Override default description for ReloadHotwireStream
     */
    public static function getDescription(): string
    {
        return 'Turbo Stream reload response';
    }
}
