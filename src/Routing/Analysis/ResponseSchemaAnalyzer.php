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

class ResponseSchemaAnalyzer extends RouteAnalysisService
{
    /**
     * Analyze response schemas for all routes
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
     * Analyze response schema for a specific route using Psalm static analysis
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

            return $this->analyzeRendererSchema($returnType, $method);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Analyze schema based on renderer type
     */
    protected function analyzeRendererSchema(string $rendererClass, ReflectionMethod $method): ?array
    {
        if (is_subclass_of($rendererClass, Json::class)) {
            return $this->analyzeJsonRendererSchema($rendererClass, $method);
        }
        
        if (is_subclass_of($rendererClass, Markup::class)) {
            return $this->analyzeMarkupRendererSchema($rendererClass, $method);
        }
        
        if (is_subclass_of($rendererClass, Redirect::class)) {
            return $this->analyzeRedirectRendererSchema($rendererClass, $method);
        }
        
        if (is_subclass_of($rendererClass, Reload::class)) {
            return $this->analyzeReloadRendererSchema($rendererClass, $method);
        }

        // Hotwire renderers
        if (is_subclass_of($rendererClass, BaseHotwireStream::class)) {
            return $this->analyzeHotwireRendererSchema($rendererClass, $method);
        }

        return null;
    }

    /**
     * Analyze JSON renderer schema by examining constructor parameters
     */
    protected function analyzeJsonRendererSchema(string $rendererClass, ReflectionMethod $method): ?array
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
                return ['type' => 'object', 'properties' => []];
            }

            if (class_exists($dataType)) {
                return $this->analyzeClassSchema($dataType);
            }

            return ['type' => $dataType];

        } catch (\Exception $e) {
            return ['type' => 'object', 'properties' => []];
        }
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
     * Get content type for a renderer class
     */
    public function getContentTypeForRenderer(string $rendererClass): string
    {
        if (is_subclass_of($rendererClass, Json::class)) {
            return PayloadStorage::JSON_HEADER_VALUE;
        }
        
        if (is_subclass_of($rendererClass, Markup::class)) {
            return PayloadStorage::HTML_HEADER_VALUE;
        }
        
        if (is_subclass_of($rendererClass, Redirect::class)) {
            return PayloadStorage::HTML_HEADER_VALUE; // Redirects don't have content, but default to HTML
        }
        
        if (is_subclass_of($rendererClass, Reload::class)) {
            return PayloadStorage::HTML_HEADER_VALUE;
        }

        // Hotwire renderers
        if (is_subclass_of($rendererClass, BaseHotwireStream::class)) {
            return BaseHotwireStream::TURBO_INDICATOR;
        }

        return PayloadStorage::HTML_HEADER_VALUE; // Default fallback
    }

    /**
     * Get status code for a renderer class
     */
    public function getStatusCodeForRenderer(string $rendererClass): int
    {
        if (is_subclass_of($rendererClass, Redirect::class)) {
            return Redirect::STATUS_CODE;
        }
        
        if (is_subclass_of($rendererClass, Reload::class)) {
            return Reload::STATUS_CODE;
        }

        if (is_subclass_of($rendererClass, RedirectHotwireStream::class)) {
            return RedirectHotwireStream::STATUS_CODE;
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
     * Analyze class schema using reflection
     */
    protected function analyzeClassSchema(string $className): array
    {
        try {
            $reflection = new ReflectionClass($className);
            $properties = [];
            $required = [];

            // Get public properties
            foreach ($reflection->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {
                $propertyName = $property->getName();
                $propertyType = $this->getPropertyType($property);
                
                $properties[$propertyName] = $this->mapPhpTypeToJsonSchema($propertyType);
                
                if (!$property->isInitialized()) {
                    $required[] = $propertyName;
                }
            }

            // Get getter methods
            foreach ($reflection->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
                $methodName = $method->getName();
                
                if (str_starts_with($methodName, 'get') && $method->getNumberOfParameters() === 0) {
                    $propertyName = lcfirst(substr($methodName, 3));
                    $returnType = $this->getReturnType($method);
                    
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
     * Get property type
     */
    protected function getPropertyType(\ReflectionProperty $property): string
    {
        $type = $property->getType();
        
        if ($type instanceof ReflectionType) {
            return $type->getName();
        }
        
        return 'mixed';
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