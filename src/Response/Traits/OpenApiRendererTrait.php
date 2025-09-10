<?php

namespace Suphle\Response\Traits;

use Suphle\Contracts\Response\OpenApiRenderer;
use Suphle\Request\PayloadStorage;

/**
 * Trait providing default OpenAPI metadata implementations
 * 
 * Renderers can use this trait to automatically provide
 * OpenAPI documentation metadata without implementing
 * the full interface manually.
 */
trait OpenApiRendererTrait
{
    /**
     * Default content type - override in renderer if needed
     */
    public static function getContentType(): string
    {
        return PayloadStorage::HTML_HEADER_VALUE;
    }

    /**
     * Default status code - override in renderer if needed
     */
    public static function getStatusCode(): int
    {
        return 200;
    }

    /**
     * Default response schema - override in renderer if needed
     */
    public static function getResponseSchema(): array
    {
        return [
            'type' => 'string',
            'description' => static::getDescription()
        ];
    }

    /**
     * Default description - override in renderer if needed
     */
    public static function getDescription(): string
    {
        $className = static::class;
        $parts = explode('\\', $className);
        $name = end($parts);
        
        return ucfirst(strtolower(preg_replace('/([a-z])([A-Z])/', '$1 $2', $name))) . ' response';
    }
} 