<?php

namespace Suphle\Contracts\Response;

/**
 * Interface for renderers to provide OpenAPI metadata
 * 
 * Renderers implementing this interface can automatically
 * provide content type, status code, and response schema
 * information for OpenAPI documentation generation.
 */
interface OpenApiRenderer
{
    /**
     * Get the content type for this renderer
     */
    public static function getContentType(): string;

    /**
     * Get the default status code for this renderer
     */
    public static function getStatusCode(): int;

    /**
     * Get the response schema for this renderer
     * 
     * @return array OpenAPI schema definition
     */
    public static function getResponseSchema(): array;

    /**
     * Get the description for this renderer type
     */
    public static function getDescription(): string;
} 