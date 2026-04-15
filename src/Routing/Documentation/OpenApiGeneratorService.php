<?php

namespace Suphle\Routing\Documentation;

use Suphle\Routing\{AttributeRouteScanner, Analysis\PsalmSchemaAnalyzer};
use Suphle\Contracts\Database\ModelSchemaDetector;
use Suphle\Exception\Diffusers\{UnauthorizedDiffuser, UnauthenticatedDiffuser};
use ReflectionClass;
use ReflectionMethod;

class OpenApiGeneratorService
{
    public function __construct(
        protected readonly AttributeRouteScanner $routeScanner,
        protected readonly PsalmSchemaAnalyzer $responseSchemaAnalyzer,
        protected readonly ModelSchemaDetector $schemaDetector
    ) {
        //
    }

    public function generateOpenApiSpec(): array
    {
        $routes = $this->getAllRoutes();
        
        $spec = [
            'openapi' => '3.0.0',
            'info' => [
                'title' => 'Suphle API Documentation',
                'version' => '1.0.0'
            ],
            'paths' => [],
            'components' => [
                // Retrieve all models registered by the detector during the route scan
                'schemas' => $this->schemaDetector->getGeneratedSchemas(),
                'parameters' => []
            ]
        ];

        foreach ($routes as $route) {
            $path = $route['path'];
            $method = strtolower($route['method']);
            
            if (!isset($spec['paths'][$path])) {
                $spec['paths'][$path] = [];
            }

            // Pass the shape directly from the route array
            $spec['paths'][$path][$method] = $this->buildPathItem($route, $route['response_shape'] ?? null);
        }

        return $spec;
    }

    protected function buildPathItem(array $route, ?array $responseSchema = null): array
    {
        $isMirror = $route['is_mirror'] ?? false;
    
        $pathItem = [
            'summary' => ($isMirror ? '[API] ' : '') . ($route['summary'] ?? ucfirst($route['handler'])),
            'description' => $isMirror 
                ? "Mirrored API endpoint for " . $route['handler'] 
                : ($route['description'] ?? ''),
            'tags' => [
                $this->extractModuleName($route['coordinator']),
                $isMirror ? 'API' : 'Web' // Additional tagging for filtering in Swagger UI
            ],
            'parameters' => $this->buildParameters($route),
            'responses' => $this->buildResponses($route, $route['response_shape'] ?? null)
        ];

        // Add request body for POST/PUT/PATCH methods
        if (in_array(strtoupper($route['method']), ['POST', 'PUT', 'PATCH'])) {
            $requestBody = $this->buildRequestBody($route);
            if (!empty($requestBody)) {
                $pathItem['requestBody'] = $requestBody;
            }
        }

        return $pathItem;
    }

    protected function buildParameters(array $route): array
    {
        $parameters = [];

        // Path parameters from placeholders
        foreach ($route['placeholders'] as $placeholder) {
            $parameters[] = [
                'name' => $placeholder,
                'in' => 'path',
                'required' => true,
                'schema' => ['type' => 'string']
            ];
        }

        // Method parameters that are not payload readers
        foreach ($route['parameters'] as $param) {
            if (empty($param['is_payload_reader'])) {
                $parameters[] = [
                    'name' => $param['name'],
                    'in' => 'query',
                    'required' => $param['required'],
                    'schema' => ['type' => $this->mapPhpTypeToOpenApi($param['type'])]
                ];
            }
        }

        return $parameters;
    }

    protected function buildRequestBody(array $route): array
    {
        $properties = [];
        $required = [];

        // Extract validation rules as schema properties - this is the primary source
        foreach ($route['validation_rules'] as $field => $rules) {
            $properties[$field] = $this->buildSchemaFromRules($rules);
            
            if (str_contains($rules, 'required')) {
                $required[] = $field;
            }
        }

        return [
            'required' => true,
            'content' => [
                'application/json' => [
                    'schema' => [
                        'type' => 'object',
                        'properties' => $properties,
                        'required' => $required
                    ]
                ]
            ]
        ];
    }

    protected function buildResponses(array $route, ?array $responseSchema = null): array
    {
        $rendererClass = $route['renderer'];
        
        // Use interface if available, fallback to analyzer
        $contentType = $this->responseSchemaAnalyzer->getContentTypeForRenderer($rendererClass);
        $statusCode = $this->responseSchemaAnalyzer->getStatusCodeForRenderer($rendererClass);
        
        $responses = [
            (string)$statusCode => [
                'description' => 'Successful response',
                'content' => $this->buildResponseContent($route, $responseSchema, $contentType)
            ]
        ];

        // Add error responses based on validation rules
        if (!empty($route['validation_rules'])) {
            $responses['422'] = [
                'description' => 'Validation error',
                'content' => [
                    'application/json' => [
                        'schema' => [
                            'type' => 'object',
                            'properties' => [
                                'message' => ['type' => 'string'],
                                'errors' => [
                                    'type' => 'object',
                                    'additionalProperties' => ['type' => 'array', 'items' => ['type' => 'string']]
                                ]
                            ]
                        ]
                    ]
                ]
            ];
        }

        // Add auth responses if route has authentication/authorization barriers
        if ($this->responseSchemaAnalyzer->hasAuthBarriers($route)) {
            $authMessage = $this->responseSchemaAnalyzer->getAuthenticationErrorMessage();
            $authzMessage = $this->responseSchemaAnalyzer->getAuthorizationErrorMessage();
            
            $responses['401'] = [
                'description' => 'Unauthorized - Authentication required',
                'content' => [
                    'application/json' => [
                        'schema' => [
                            'type' => 'object',
                            'properties' => [
                                Unauthenticated::ERRORS_PRESENCE => [
                                    'type' => 'string', 
                                    'example' => $authMessage
                                ]
                            ]
                        ]
                    ]
                ]
            ];

            $responses['403'] = [
                'description' => 'Forbidden - Insufficient permissions',
                'content' => [
                    'application/json' => [
                        'schema' => [
                            'type' => 'object',
                            'properties' => [
                                UnauthorizedDiffuser::ERRORS_PRESENCE => [
                                    'type' => 'string', 
                                    'example' => $authzMessage
                                ]
                            ]
                        ]
                    ]
                ]
            ];
        }

        return $responses;
    }

    protected function buildResponseContent(array $route, ?array $responseSchema = null, string $contentType = 'application/json'): array
    {
        $rendererType = $route['response_shape']['type'] ?? 'unknown';
        
        switch ($rendererType) {
            case 'json':
                return [
                    $contentType => [
                        'schema' => $responseSchema ?? ['type' => 'object']
                    ]
                ];
            
            case 'html':
                return [
                    $contentType => [
                        'schema' => ['type' => 'string']
                    ]
                ];
            
            case 'redirect':
                return [
                    $contentType => [
                        'schema' => [
                            'type' => 'string',
                            'description' => 'Empty response with Location header'
                        ]
                    ]
                ];
            
            default:
                return [
                    $contentType => [
                        'schema' => ['type' => 'object']
                    ]
                ];
        }
    }

    protected function buildSchemaFromRules(string $rules): array
    {
        $schema = ['type' => 'string'];

        if (str_contains($rules, 'integer')) {
            $schema['type'] = 'integer';
        } elseif (str_contains($rules, 'numeric')) {
            $schema['type'] = 'number';
        } elseif (str_contains($rules, 'boolean')) {
            $schema['type'] = 'boolean';
        } elseif (str_contains($rules, 'array')) {
            $schema['type'] = 'array';
            $schema['items'] = ['type' => 'object'];
        }

        // Add format for email
        if (str_contains($rules, 'email')) {
            $schema['format'] = 'email';
        }

        // Add format for date
        if (str_contains($rules, 'date')) {
            $schema['format'] = 'date';
        }

        return $schema;
    }

    protected function mapPhpTypeToOpenApi(string $phpType): string
    {
        return match ($phpType) {
            'string' => 'string',
            'int', 'integer' => 'integer',
            'float', 'double' => 'number',
            'bool', 'boolean' => 'boolean',
            'array' => 'array',
            default => 'string'
        };
    }

    protected function extractModuleName(string $coordinatorClass): string
    {
        $parts = explode('\\', $coordinatorClass);
        return $parts[1] ?? 'Default';
    }

    public function getAllRoutes(): array
    {
        return $this->routeScanner->scanModulesByPath(
            fn (Container $container) => $container->getClass(RouterConfig::class)
            ->getCoordinatorPath(),
            
            $this->responseSchemaAnalyzer->analyzeCoordinator(...)
        );
    }
} 