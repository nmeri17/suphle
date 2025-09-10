<?php

namespace Suphle\Tests\Unit\Routing\Analysis;

use PHPUnit\Framework\TestCase;
use Suphle\Routing\Analysis\PsalmSchemaAnalyzer;
use Suphle\Contracts\Response\OpenApiRenderer;
use Suphle\Response\Traits\OpenApiRendererTrait;
use Suphle\Request\PayloadStorage;
use ReflectionMethod;

class PsalmSchemaAnalyzerInterfaceTest extends TestCase
{
    private PsalmSchemaAnalyzer $analyzer;

    protected function setUp(): void
    {
        $this->analyzer = new PsalmSchemaAnalyzer();
    }

    public function test_interface_renderer_content_type_detection()
    {
        $customRenderer = new class implements OpenApiRenderer {
            use OpenApiRendererTrait;

            public static function getContentType(): string
            {
                return 'application/custom';
            }
        };

        $contentType = $this->analyzer->getContentTypeForRenderer(get_class($customRenderer));
        $this->assertEquals('application/custom', $contentType);
    }

    public function test_interface_renderer_status_code_detection()
    {
        $customRenderer = new class implements OpenApiRenderer {
            use OpenApiRendererTrait;

            public static function getStatusCode(): int
            {
                return 201;
            }
        };

        $statusCode = $this->analyzer->getStatusCodeForRenderer(get_class($customRenderer));
        $this->assertEquals(201, $statusCode);
    }

    public function test_interface_renderer_schema_detection()
    {
        $customRenderer = new class implements OpenApiRenderer {
            use OpenApiRendererTrait;

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
        };

        $method = new ReflectionMethod($customRenderer, '__construct');
        $schema = $this->analyzer->analyzeRendererSchemaWithPsalm(get_class($customRenderer), $method);
        
        $this->assertIsArray($schema);
        $this->assertEquals('object', $schema['type']);
        $this->assertArrayHasKey('properties', $schema);
    }

    public function test_fallback_to_legacy_analysis_for_non_interface_renderers()
    {
        // Create a renderer that doesn't implement OpenApiRenderer
        $legacyRenderer = new class {
            public function render(): string
            {
                return 'legacy';
            }
        };

        $contentType = $this->analyzer->getContentTypeForRenderer(get_class($legacyRenderer));
        $this->assertEquals(PayloadStorage::HTML_HEADER_VALUE, $contentType); // Default fallback

        $statusCode = $this->analyzer->getStatusCodeForRenderer(get_class($legacyRenderer));
        $this->assertEquals(200, $statusCode); // Default fallback
    }

    public function test_interface_takes_precedence_over_legacy_mapping()
    {
        $customRenderer = new class implements OpenApiRenderer {
            use OpenApiRendererTrait;

            public static function getContentType(): string
            {
                return 'application/override';
            }

            public static function getStatusCode(): int
            {
                return 418; // I'm a teapot
            }
        };

        // Even if this renderer extends a class in the legacy mapping,
        // the interface should take precedence
        $contentType = $this->analyzer->getContentTypeForRenderer(get_class($customRenderer));
        $this->assertEquals('application/override', $contentType);

        $statusCode = $this->analyzer->getStatusCodeForRenderer(get_class($customRenderer));
        $this->assertEquals(418, $statusCode);
    }

    public function test_interface_renderer_with_complex_schema()
    {
        $complexRenderer = new class implements OpenApiRenderer {
            use OpenApiRendererTrait;

            public static function getResponseSchema(): array
            {
                return [
                    'type' => 'object',
                    'properties' => [
                        'data' => [
                            'type' => 'array',
                            'items' => [
                                'type' => 'object',
                                'properties' => [
                                    'id' => ['type' => 'integer'],
                                    'name' => ['type' => 'string'],
                                    'email' => ['type' => 'string', 'format' => 'email']
                                ]
                            ]
                        ],
                        'meta' => [
                            'type' => 'object',
                            'properties' => [
                                'total' => ['type' => 'integer'],
                                'page' => ['type' => 'integer']
                            ]
                        ]
                    ],
                    'required' => ['data']
                ];
            }
        };

        $method = new ReflectionMethod($complexRenderer, '__construct');
        $schema = $this->analyzer->analyzeRendererSchemaWithPsalm(get_class($complexRenderer), $method);
        
        $this->assertIsArray($schema);
        $this->assertEquals('object', $schema['type']);
        $this->assertArrayHasKey('data', $schema['properties']);
        $this->assertArrayHasKey('meta', $schema['properties']);
        $this->assertContains('data', $schema['required']);
    }
} 