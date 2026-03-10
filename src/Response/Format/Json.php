<?php

namespace Suphle\Response\Format;

use Suphle\Request\PayloadStorage;
use Suphle\Contracts\Response\OpenApiRenderer;
use Suphle\Response\Traits\OpenApiRendererTrait;

class Json extends GenericRenderer implements OpenApiRenderer
{
    use OpenApiRendererTrait;

    public const STATUS_CODE = 200;

    protected bool $shouldDeferValidationFailure = false;

    protected int $statusCode = self::STATUS_CODE;

    public function __construct(array $data)
    {
        $this->setRawResponse($data);

        $this->setHeaders(self::STATUS_CODE, [
            PayloadStorage::CONTENT_TYPE_KEY => PayloadStorage::JSON_HEADER_VALUE
        ]);
    }

    public function render(): string
    {
        return $this->renderJson();
    }

    /**
     * {@inheritdoc}
     */
    public function deferValidationContent(): bool
    {
        return false;
    }

    /**
     * Override default content type for JSON
     */
    public static function getContentType(): string
    {
        return PayloadStorage::JSON_HEADER_VALUE;
    }

    /**
     * Override default response schema for JSON
     */
    public static function getResponseSchema(): array
    {
        return [
            'type' => 'object',
            'description' => static::getDescription()
        ];
    }

    /**
     * Override default description for JSON
     */
    public static function getDescription(): string
    {
        return 'JSON response';
    }
}
