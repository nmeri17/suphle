<?php
namespace Suphle\Routing\Documentation;

use Suphle\Routing\{AttributeRouteScanner, Analysis\PsalmSchemaAnalyzer};
use Suphle\Request\PayloadStorage;
use Suphle\Contracts\Database\ModelSchemaDetector;
use Suphle\Exception\Diffusers\{UnauthorizedDiffuser, UnauthenticatedDiffuser};
use ReflectionClass, ReflectionMethod;

class OpenApiGeneratorService
{
    public function __construct(
        protected readonly AttributeRouteScanner $routeScanner,
        protected readonly PsalmSchemaAnalyzer $psalmAnalyzer,
        protected readonly ModelSchemaDetector $schemaDetector
    ) {
        //
    }

    public function generateOpenApiSpec (string $baseUrl): array // $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'];
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
                'parameters' => [],
                'securitySchemes' => [
                    'bearerAuth' => [
                        'type' => 'http',
                        'scheme' => 'bearer',
                        'bearerFormat' => 'JWT'
                    ]
                ]
            ],
            'servers' => [
                [
                    'url' => $baseUrl,
                    'description' => 'Current server'
                ]
            ],
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
                class_basename($route['coordinator']),
                $isMirror ? 'API' : 'Web' // Additional tagging for filtering in Swagger UI
            ],
            'parameters' => $this->buildParameters($route),
            'responses' => $this->buildResponses($route, $responseSchema),
            'operationId' => $this->buildOperationId($route),
        ];

        // Add request body for POST/PUT/PATCH methods
        if (in_array(strtoupper($route['method']), ['POST', 'PUT', 'PATCH'])) {
            $requestBody = $this->buildRequestBody($route);
            if (!empty($requestBody)) {
                $pathItem['requestBody'] = $requestBody;
            }
        }
        if ($this->psalmAnalyzer->hasAuthBarriers($route)) {
            $pathItem['security'] = [
                ['bearerAuth' => []]
            ];
        }

        return $pathItem;
    }

    // required by postman and sdk generators
    protected function buildOperationId(array $route): string
    {
        return strtolower($route['method']) . '_' .

        str_replace(['\\', '@'], '_', $route['handler']);
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
            'required' => !empty($required),
            'content' => [
                PayloadStorage::JSON_HEADER_VALUE => [
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
        
        $contentTypeSchema = $this->psalmAnalyzer->getStandardFormatSchema($rendererClass);

        $contentType = is_array($contentTypeSchema)
            && ($contentTypeSchema['contentMediaType'] ?? null)
                ? $contentTypeSchema['contentMediaType']
                : PayloadStorage::JSON_HEADER_VALUE;

        $statusCode = $rendererClass::STATUS_CODE;
        
        $responses = [
            (string)$statusCode => [
                'description' => $this->psalmAnalyzer->extractMethodDescription(),

                'content' => $this->buildResponseContent($responseSchema, $contentType)
            ]
        ];

        // Add error responses based on validation rules
        if (!empty($route['validation_rules'])) {
            $responses['422'] = [
                'description' => 'Validation error',
                'content' => [
                    PayloadStorage::JSON_HEADER_VALUE => [
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
        if ($this->psalmAnalyzer->hasAuthBarriers($route)) {
            $authMessage = $this->psalmAnalyzer->getAuthenticationErrorMessage();
            $authzMessage = $this->psalmAnalyzer->getAuthorizationErrorMessage();
            
            $responses['401'] = [
                'description' => 'Unauthorized - Authentication required',
                'content' => [
                    PayloadStorage::JSON_HEADER_VALUE => [
                        'schema' => [
                            'type' => 'object',
                            'properties' => [
                                UnauthenticatedDiffuser::ERRORS_PRESENCE => [
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
                    PayloadStorage::JSON_HEADER_VALUE => [
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

    protected function buildResponseContent(string $contentType, ?array $responseSchema = null): array {
        return [
            $contentType => [
                'schema' => $responseSchema ?? ['type' => 'object']
            ]
        ];
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

        if (str_contains($rules, 'email')) {
            $schema['format'] = 'email';
        }

        if (str_contains($rules, 'date')) {
            $schema['format'] = 'date';
        }

        if (str_contains($rules, 'nullable')) {
            $schema['nullable'] = true;
        }

        $schema = $this->setMinMaxDetails($rules, $schema);

        $schema = $this->setEnumDetails($rules, $schema);

        return $schema;
    }

    protected function setMinMaxDetails (string $rules, array $schema):array {

        $type = $schema['type'];

        if (preg_match('/min:(\d+)/', $rules, $m)) {
            $value = (int) $m[1];

            match ($type) {
                'string' => $schema['minLength'] = $value,
                'array'  => $schema['minItems'] = $value,
                default  => $schema['minimum'] = $value,
            };
        }

        if (preg_match('/max:(\d+)/', $rules, $m)) {
            $value = (int) $m[1];

            match ($type) {
                'string' => $schema['maxLength'] = $value,
                'array'  => $schema['maxItems'] = $value,
                default  => $schema['maximum'] = $value,
            };
        }
        return $schema;
    }

    protected function setEnumDetails (string $rules, array $schema):array {

        $type = $schema['type'];

        if (preg_match('/in:([^|]+)/', $rules, $m)) {
            $values = explode(',', $m[1]);

            if ($type === 'array') {
                $schema['items']['enum'] = $values;
            } else {
                $schema['enum'] = $values;
            }
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

    public function getAllRoutes(): array
    {
        return $this->routeScanner->scanModulesByPath(
            fn (Container $container) => $container->getClass(RouterConfig::class)
            ->getCoordinatorPath(),
            
            $this->psalmAnalyzer->analyzeCoordinator(...)
        );
    }
} 