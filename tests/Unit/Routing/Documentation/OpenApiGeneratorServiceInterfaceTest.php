<?php

namespace Suphle\Tests\Unit\Routing\Documentation;

use PHPUnit\Framework\TestCase;
use Suphle\Routing\{RouteDetailsService, Documentation\OpenApiGeneratorService};
use Suphle\Routing\Analysis\PsalmSchemaAnalyzer;
use Suphle\Contracts\Response\OpenApiRenderer;
use Suphle\Response\Traits\OpenApiRendererTrait;
use Suphle\Request\PayloadStorage;

class OpenApiGeneratorServiceInterfaceTest extends TestCase
{
    private OpenApiGeneratorService $generator;
    private RouteDetailsService $routeDetailsService;
    private PsalmSchemaAnalyzer $schemaAnalyzer;

    protected function setUp(): void
    {
        $this->routeDetailsService = $this->createMock(RouteDetailsService::class);
        $this->schemaAnalyzer = $this->createMock(PsalmSchemaAnalyzer::class);
        
        $this->generator = new OpenApiGeneratorService(
            $this->routeDetailsService,
            $this->schemaAnalyzer
        );
    }

    public function test_interface_renderer_metadata_extraction()
    {
        $customRenderer = new class implements OpenApiRenderer {
            use OpenApiRendererTrait;

            public static function getContentType(): string
            {
                return 'application/custom';
            }

            public static function getStatusCode(): int
            {
                return 201;
            }

            public static function getResponseSchema(): array
            {
                return [
                    'type' => 'object',
                    'properties' => [
                        'id' => ['type' => 'integer'],
                        'name' => ['type' => 'string']
                    ]
                ];
            }

            public static function getDescription(): string
            {
                return 'Custom API response';
            }
        };

        $route = [
            'path' => '/test',
            'method' => 'GET',
            'renderer' => get_class($customRenderer),
            'coordinator' => 'Test\\Coordinator',
            'handler' => 'testMethod',
            'placeholders' => [],
            'parameters' => [],
            'validation_rules' => [],
            'response_shape' => ['type' => 'json']
        ];

        $this->routeDetailsService->method('getAllDetailedRoutes')
            ->willReturn([$route]);

        $this->schemaAnalyzer->method('analyzeResponseSchemas')
            ->willReturn([]);

        $this->schemaAnalyzer->method('generateModelSchemas')
            ->willReturn([]);

        $spec = $this->generator->generateOpenApiSpec();

        $this->assertArrayHasKey('paths', $spec);
        $this->assertArrayHasKey('/test', $spec['paths']);
        $this->assertArrayHasKey('get', $spec['paths']['/test']);

        $pathItem = $spec['paths']['/test']['get'];
        $this->assertArrayHasKey('responses', $pathItem);
        $this->assertArrayHasKey('201', $pathItem['responses']); // Status code from interface
    }

    public function test_fallback_to_analyzer_for_non_interface_renderers()
    {
        $legacyRenderer = new class {
            public function render(): string
            {
                return 'legacy';
            }
        };

        $route = [
            'path' => '/legacy',
            'method' => 'POST',
            'renderer' => get_class($legacyRenderer),
            'coordinator' => 'Test\\Coordinator',
            'handler' => 'legacyMethod',
            'placeholders' => [],
            'parameters' => [],
            'validation_rules' => [],
            'response_shape' => ['type' => 'html']
        ];

        $this->routeDetailsService->method('getAllDetailedRoutes')
            ->willReturn([$route]);

        $this->schemaAnalyzer->method('analyzeResponseSchemas')
            ->willReturn([]);

        $this->schemaAnalyzer->method('generateModelSchemas')
            ->willReturn([]);

        // Mock the analyzer methods for legacy renderer
        $this->schemaAnalyzer->method('getContentTypeForRenderer')
            ->with(get_class($legacyRenderer))
            ->willReturn(PayloadStorage::HTML_HEADER_VALUE);

        $this->schemaAnalyzer->method('getStatusCodeForRenderer')
            ->with(get_class($legacyRenderer))
            ->willReturn(200);

        $spec = $this->generator->generateOpenApiSpec();

        $this->assertArrayHasKey('/legacy', $spec['paths']);
        $this->assertArrayHasKey('post', $spec['paths']['/legacy']);
        
        $pathItem = $spec['paths']['/legacy']['post'];
        $this->assertArrayHasKey('responses', $pathItem);
        $this->assertArrayHasKey('200', $pathItem['responses']); // Status code from analyzer
    }

    public function test_interface_renderer_with_validation_rules()
    {
        $customRenderer = new class implements OpenApiRenderer {
            use OpenApiRendererTrait;

            public static function getContentType(): string
            {
                return 'application/json';
            }

            public static function getStatusCode(): int
            {
                return 200;
            }

            public static function getResponseSchema(): array
            {
                return [
                    'type' => 'object',
                    'properties' => [
                        'message' => ['type' => 'string']
                    ]
                ];
            }
        };

        $route = [
            'path' => '/users',
            'method' => 'POST',
            'renderer' => get_class($customRenderer),
            'coordinator' => 'User\\Coordinator',
            'handler' => 'createUser',
            'placeholders' => [],
            'parameters' => [],
            'validation_rules' => [
                'name' => 'required|string|max:255',
                'email' => 'required|email',
                'age' => 'integer|min:18'
            ],
            'response_shape' => ['type' => 'json']
        ];

        $this->routeDetailsService->method('getAllDetailedRoutes')
            ->willReturn([$route]);

        $this->schemaAnalyzer->method('analyzeResponseSchemas')
            ->willReturn([]);

        $this->schemaAnalyzer->method('generateModelSchemas')
            ->willReturn([]);

        $spec = $this->generator->generateOpenApiSpec();

        $pathItem = $spec['paths']['/users']['post'];
        
        // Check request body from validation rules
        $this->assertArrayHasKey('requestBody', $pathItem);
        $requestBody = $pathItem['requestBody'];
        $this->assertTrue($requestBody['required']);
        
        $schema = $requestBody['content']['application/json']['schema'];
        $this->assertArrayHasKey('name', $schema['properties']);
        $this->assertArrayHasKey('email', $schema['properties']);
        $this->assertArrayHasKey('age', $schema['properties']);
        $this->assertContains('name', $schema['required']);
        $this->assertContains('email', $schema['required']);

        // Check validation error response
        $this->assertArrayHasKey('422', $pathItem['responses']);
    }

    public function test_interface_renderer_with_path_parameters()
    {
        $customRenderer = new class implements OpenApiRenderer {
            use OpenApiRendererTrait;

            public static function getContentType(): string
            {
                return 'application/json';
            }
        };

        $route = [
            'path' => '/users/{id}/posts/{postId}',
            'method' => 'GET',
            'renderer' => get_class($customRenderer),
            'coordinator' => 'User\\Coordinator',
            'handler' => 'getUserPost',
            'placeholders' => ['id', 'postId'],
            'parameters' => [
                [
                    'name' => 'include',
                    'type' => 'string',
                    'required' => false,
                    'is_payload_reader' => false
                ]
            ],
            'validation_rules' => [],
            'response_shape' => ['type' => 'json']
        ];

        $this->routeDetailsService->method('getAllDetailedRoutes')
            ->willReturn([$route]);

        $this->schemaAnalyzer->method('analyzeResponseSchemas')
            ->willReturn([]);

        $this->schemaAnalyzer->method('generateModelSchemas')
            ->willReturn([]);

        $spec = $this->generator->generateOpenApiSpec();

        $pathItem = $spec['paths']['/users/{id}/posts/{postId}']['get'];
        
        // Check path parameters
        $parameters = $pathItem['parameters'];
        $this->assertCount(3, $parameters); // 2 path + 1 query
        
        $pathParams = array_filter($parameters, fn($p) => $p['in'] === 'path');
        $this->assertCount(2, $pathParams);
        
        $queryParams = array_filter($parameters, fn($p) => $p['in'] === 'query');
        $this->assertCount(1, $queryParams);
    }

    public function test_interface_renderer_with_auth_barriers()
    {
        $customRenderer = new class implements OpenApiRenderer {
            use OpenApiRendererTrait;

            public static function getContentType(): string
            {
                return 'application/json';
            }
        };

        $route = [
            'path' => '/admin/users',
            'method' => 'GET',
            'renderer' => get_class($customRenderer),
            'coordinator' => 'Admin\\Coordinator',
            'handler' => 'listUsers',
            'placeholders' => [],
            'parameters' => [],
            'validation_rules' => [],
            'response_shape' => ['type' => 'json'],
            'middleware' => ['auth', 'can:manage-users']
        ];

        $this->routeDetailsService->method('getAllDetailedRoutes')
            ->willReturn([$route]);

        $this->schemaAnalyzer->method('analyzeResponseSchemas')
            ->willReturn([]);

        $this->schemaAnalyzer->method('generateModelSchemas')
            ->willReturn([]);

        $this->schemaAnalyzer->method('hasAuthBarriers')
            ->willReturn(true);

        $this->schemaAnalyzer->method('getAuthenticationErrorMessage')
            ->willReturn('Authentication required');

        $this->schemaAnalyzer->method('getAuthorizationErrorMessage')
            ->willReturn('Insufficient permissions');

        $spec = $this->generator->generateOpenApiSpec();

        $pathItem = $spec['paths']['/admin/users']['get'];
        
        // Check auth error responses
        $this->assertArrayHasKey('401', $pathItem['responses']);
        $this->assertArrayHasKey('403', $pathItem['responses']);
        
        $this->assertEquals('Authentication required', 
            $pathItem['responses']['401']['content']['application/json']['schema']['properties']['message']['example']);
        
        $this->assertEquals('Insufficient permissions', 
            $pathItem['responses']['403']['content']['application/json']['schema']['properties']['errors']['example']);
    }
} 