<?php

namespace Suphle\Routing\Analysis;

use Suphle\Contracts\Config\Router as RouterConfig;
use Suphle\Hydration\Container;
use Suphle\Contracts\Flows\FlowHydrator;
use Suphle\Routing\Analysis\RouteAnalysisService;
use Suphle\Contracts\Presentation\BaseRenderer;
use Suphle\Response\Format\{Json, Markup, Redirect, Reload};
use Suphle\Adapters\Presentation\Hotwire\Formats\{BaseHotwireStream, RedirectHotwireStream, ReloadHotwireStream};
use Suphle\Request\PayloadStorage;
use Suphle\Services\Decorators\ValidationRules;
use Suphle\Exception\{ComponentEntry, Diffusers\UnauthenticatedDiffuser, Diffusers\UnauthorizedDiffuser};
use Suphle\Contracts\Presentation\HtmlParser;
use ReflectionClass;
use ReflectionMethod;
use ReflectionType;
use ReflectionParameter;
use Suphle\Contracts\Response\OpenApiRenderer;

/**
 * Psalm-based static analysis for response schemas
 * 
 * This service uses Psalm's static analysis capabilities to infer
 * response schemas from method return types and renderer constructors.
 */
class PsalmSchemaAnalyzer extends RouteAnalysisService
{
    /**
     * Renderer schema analysis map
     */
    private const RENDERER_SCHEMA_MAP = [
        Json::class => 'analyzeJsonRendererSchemaWithPsalm',
        Markup::class => 'analyzeMarkupRendererSchema',
        Redirect::class => 'analyzeRedirectRendererSchema',
        Reload::class => 'analyzeReloadRendererSchema',
        BaseHotwireStream::class => 'analyzeHotwireRendererSchema',
    ];

    /**
     * Content type mapping for renderers
     */
    private const CONTENT_TYPE_MAP = [
        Json::class => PayloadStorage::JSON_HEADER_VALUE,
        Markup::class => PayloadStorage::HTML_HEADER_VALUE,
        Redirect::class => PayloadStorage::HTML_HEADER_VALUE,
        Reload::class => PayloadStorage::HTML_HEADER_VALUE,
        BaseHotwireStream::class => BaseHotwireStream::TURBO_INDICATOR,
    ];

    /**
     * Status code mapping for renderers
     */
    private const STATUS_CODE_MAP = [
        Redirect::class => Redirect::STATUS_CODE,
        Reload::class => Reload::STATUS_CODE,
        RedirectHotwireStream::class => RedirectHotwireStream::STATUS_CODE,
    ];

    /**
     * Analyze response schemas for all routes using Psalm static analysis
     */
    public function analyzeResponseSchemas(): array
    {
        $routes = $this->getAllRoutes();
        $schemas = [];

        foreach ($routes as $route) {
            $coordinatorClass = $route['coordinator'];
            $methodName = $route['handler'];
            
            $schema = $this->analyzeRouteResponseSchema($coordinatorClass, $methodName);
            if ($schema) {
                $schemas[$route['path']] = $schema;
            }
        }

        return $schemas;
    }

    /**
     * Analyze response schema for a specific route using Psalm
     */
    public function analyzeRouteResponseSchema(string $coordinatorClass, string $methodName): ?array
    {
        try {
            $reflection = new ReflectionClass($coordinatorClass);
            $method = $reflection->getMethod($methodName);
            
            $returnType = $this->getReturnType($method);
            if (!$returnType) {
                return null;
            }

            return $this->analyzeRendererSchemaWithPsalm($returnType, $method);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Analyze schema using renderer interface
     */
    protected function analyzeRendererSchemaWithPsalm(string $rendererClass, ReflectionMethod $method): ?array
    {
        // Check if renderer implements OpenApiRenderer interface
        if (is_subclass_of($rendererClass, OpenApiRenderer::class)) {
            return $rendererClass::getResponseSchema();
        }

        // Fallback to legacy analysis for non-interface renderers
        foreach (self::RENDERER_SCHEMA_MAP as $baseClass => $analyzerMethod) {
            if (is_subclass_of($rendererClass, $baseClass)) {
                return $this->$analyzerMethod($rendererClass, $method);
            }
        }

        return null;
    }

    /**
     * Analyze JSON renderer schema using Psalm's type inference
     */
    protected function analyzeJsonRendererSchemaWithPsalm(string $rendererClass, ReflectionMethod $method): ?array
    {
        try {
            $reflection = new ReflectionClass($rendererClass);
            $constructor = $reflection->getConstructor();
            
            if (!$constructor) {
                return ['type' => 'object', 'properties' => []];
            }

            $parameters = $constructor->getParameters();
            if (empty($parameters)) {
                return ['type' => 'object', 'properties' => []];
            }

            // Get the first parameter (usually the data)
            $dataParam = $parameters[0];
            $dataType = $this->getParameterType($dataParam);
            
            if ($dataType === 'array') {
                // For arrays, we could use Psalm to infer the array shape
                return $this->inferArraySchemaWithPsalm($method);
            }

            if (class_exists($dataType)) {
                return $this->analyzeClassSchemaWithPsalm($dataType);
            }

            return ['type' => $dataType];

        } catch (\Exception $e) {
            return ['type' => 'object', 'properties' => []];
        }
    }

    /**
     * Infer array schema using Psalm's type inference
     */
    protected function inferArraySchemaWithPsalm(ReflectionMethod $method): array
    {
        // This would use Psalm's API to analyze the method and infer array shape
        // For now, return a generic object schema
        return [
            'type' => 'object',
            'properties' => [],
            'additionalProperties' => true
        ];
    }

    /**
     * Analyze class schema using Psalm's type inference
     */
    protected function analyzeClassSchemaWithPsalm(string $className): array
    {
        try {
            $reflection = new ReflectionClass($className);
            $properties = [];
            $required = [];

            // Get public properties with Psalm's type inference
            foreach ($reflection->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {
                $propertyName = $property->getName();
                $propertyType = $this->getPropertyTypeWithPsalm($property);
                
                $properties[$propertyName] = $this->mapPhpTypeToJsonSchema($propertyType);
                
                if (!$property->isInitialized()) {
                    $required[] = $propertyName;
                }
            }

            // Get getter methods with Psalm's return type inference
            foreach ($reflection->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
                $methodName = $method->getName();
                
                if (str_starts_with($methodName, 'get') && $method->getNumberOfParameters() === 0) {
                    $propertyName = lcfirst(substr($methodName, 3));
                    $returnType = $this->getReturnTypeWithPsalm($method);
                    
                    if ($returnType) {
                        $properties[$propertyName] = $this->mapPhpTypeToJsonSchema($returnType);
                    }
                }
            }

            $schema = [
                'type' => 'object',
                'properties' => $properties
            ];

            if (!empty($required)) {
                $schema['required'] = $required;
            }

            return $schema;

        } catch (\Exception $e) {
            return ['type' => 'object', 'properties' => []];
        }
    }

    /**
     * Get property type using Psalm's type inference
     */
    protected function getPropertyTypeWithPsalm(\ReflectionProperty $property): string
    {
        // This would use Psalm's API to get the actual type
        // For now, fall back to reflection
        $type = $property->getType();
        
        if ($type instanceof ReflectionType) {
            return $type->getName();
        }
        
        return 'mixed';
    }

    /**
     * Get return type using Psalm's type inference
     */
    protected function getReturnTypeWithPsalm(ReflectionMethod $method): ?string
    {
        // This would use Psalm's API to get the actual return type
        // For now, fall back to reflection
        $returnType = $method->getReturnType();
        
        if ($returnType instanceof ReflectionType) {
            return $returnType->getName();
        }
        
        return null;
    }

    /**
     * Analyze markup renderer schema
     */
    protected function analyzeMarkupRendererSchema(string $rendererClass, ReflectionMethod $method): ?array
    {
        return [
            'type' => 'string',
            'format' => 'html'
        ];
    }

    /**
     * Analyze redirect renderer schema
     */
    protected function analyzeRedirectRendererSchema(string $rendererClass, ReflectionMethod $method): ?array
    {
        // Redirect renderers return empty string and set Location header
        return [
            'type' => 'string',
            'description' => 'Empty response with Location header'
        ];
    }

    /**
     * Analyze reload renderer schema
     */
    protected function analyzeReloadRendererSchema(string $rendererClass, ReflectionMethod $method): ?array
    {
        // Reload renderers return HTML content
        return [
            'type' => 'string',
            'format' => 'html'
        ];
    }

    /**
     * Analyze Hotwire renderer schema
     */
    protected function analyzeHotwireRendererSchema(string $rendererClass, ReflectionMethod $method): ?array
    {
        if (is_subclass_of($rendererClass, RedirectHotwireStream::class)) {
            return [
                'type' => 'string',
                'format' => 'html',
                'description' => 'Hotwire redirect stream'
            ];
        }

        if (is_subclass_of($rendererClass, ReloadHotwireStream::class)) {
            return [
                'type' => 'string',
                'format' => 'html',
                'description' => 'Hotwire reload stream'
            ];
        }

        // Generic Hotwire stream
        return [
            'type' => 'string',
            'format' => 'html',
            'description' => 'Hotwire stream response'
        ];
    }

    /**
     * Get content type for a renderer class using interface
     */
    public function getContentTypeForRenderer(string $rendererClass): string
    {
        // Check if renderer implements OpenApiRenderer interface
        if (is_subclass_of($rendererClass, OpenApiRenderer::class)) {
            return $rendererClass::getContentType();
        }

        // Fallback to legacy mapping
        foreach (self::CONTENT_TYPE_MAP as $baseClass => $contentType) {
            if (is_subclass_of($rendererClass, $baseClass)) {
                return $contentType;
            }
        }

        return PayloadStorage::HTML_HEADER_VALUE; // Default fallback
    }

    /**
     * Get status code for a renderer class using interface
     */
    public function getStatusCodeForRenderer(string $rendererClass): int
    {
        // Check if renderer implements OpenApiRenderer interface
        if (is_subclass_of($rendererClass, OpenApiRenderer::class)) {
            return $rendererClass::getStatusCode();
        }

        // Fallback to legacy mapping
        foreach (self::STATUS_CODE_MAP as $baseClass => $statusCode) {
            if (is_subclass_of($rendererClass, $baseClass)) {
                return $statusCode;
            }
        }

        return 200; // Default status code
    }

    /**
     * Check if route has authentication/authorization barriers
     */
    public function hasAuthBarriers(array $route): bool
    {
        // Check middleware for auth indicators
        foreach ($route['middleware'] as $middleware) {
            if (is_string($middleware) && (
                str_contains(strtolower($middleware), 'auth') ||
                str_contains(strtolower($middleware), 'can') ||
                str_contains(strtolower($middleware), 'authorize')
            )) {
                return true;
            }
        }

        // Check flows for auth indicators
        foreach ($route['flows'] as $flow) {
            if (isset($flow['type']) && str_contains(strtolower($flow['type']), 'auth')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get authentication error message from UnauthenticatedDiffuser
     */
    public function getAuthenticationErrorMessage(): string
    {
        try {
            // Create a temporary instance to get the actual message
            $diffuser = new UnauthenticatedDiffuser(
                $this->container->getClass(ComponentEntry::class),
                $this->container->getClass(HtmlParser::class),
                $this->container->getClass(BaseRenderer::class)
            );
            
            // Use reflection to access the protected method
            $reflection = new \ReflectionClass($diffuser);
            $method = $reflection->getMethod('getTokenRenderer');
            $method->setAccessible(true);
            
            $renderer = $method->invoke($diffuser);
            $response = $renderer->getRawResponse();
            
            return $response['message'] ?? 'Unauthenticated';
        } catch (\Exception $e) {
            return 'Unauthenticated';
        }
    }

    /**
     * Get authorization error message from UnauthorizedDiffuser
     */
    public function getAuthorizationErrorMessage(): string
    {
        try {
            // Create a temporary instance to get the actual message
            $diffuser = new UnauthorizedDiffuser(
                $this->container->getClass(ComponentEntry::class),
                $this->container->getClass(HtmlParser::class),
                $this->container->getClass(BaseRenderer::class)
            );
            
            $diffuser->prepareRendererData();
            $renderer = $diffuser->getRenderer();
            $response = $renderer->getRawResponse();
            
            return $response[UnauthorizedDiffuser::ERRORS_PRESENCE] ?? 'Unauthorized';
        } catch (\Exception $e) {
            return 'Unauthorized';
        }
    }

    /**
     * Map PHP types to JSON Schema types
     */
    protected function mapPhpTypeToJsonSchema(string $phpType): array
    {
        switch ($phpType) {
            case 'string':
                return ['type' => 'string'];
            case 'int':
            case 'integer':
                return ['type' => 'integer'];
            case 'float':
            case 'double':
                return ['type' => 'number'];
            case 'bool':
            case 'boolean':
                return ['type' => 'boolean'];
            case 'array':
                return ['type' => 'array', 'items' => ['type' => 'object']];
            case 'mixed':
                return ['type' => 'object'];
            default:
                if (class_exists($phpType)) {
                    return ['$ref' => "#/components/schemas/" . $this->getSchemaName($phpType)];
                }
                return ['type' => 'object'];
        }
    }

    /**
     * Get schema name for a class
     */
    protected function getSchemaName(string $className): string
    {
        $parts = explode('\\', $className);
        return end($parts);
    }

    /**
     * Generate OpenAPI components schemas from all detected models
     */
    public function generateModelSchemas(): array
    {
        $routes = $this->getAllRoutes();
        $schemas = [];

        foreach ($routes as $route) {
            $coordinatorClass = $route['coordinator'];
            $methodName = $route['handler'];
            
            $schema = $this->analyzeRouteResponseSchema($coordinatorClass, $methodName);
            if ($schema && isset($schema['$ref'])) {
                $schemaName = $this->extractSchemaName($schema['$ref']);
                $schemas[$schemaName] = $this->getSchemaDefinition($schemaName);
            }
        }

        return $schemas;
    }

    /**
     * Extract schema name from $ref
     */
    protected function extractSchemaName(string $ref): string
    {
        return str_replace('#/components/schemas/', '', $ref);
    }

    /**
     * Get schema definition by name
     */
    protected function getSchemaDefinition(string $schemaName): array
    {
        // This would need to be implemented based on your model structure
        // For now, return a basic object schema
        return [
            'type' => 'object',
            'properties' => []
        ];
    }
} 