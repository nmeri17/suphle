<?php

namespace Suphle\Tests\Unit\Routing\Analysis;

use PHPUnit\Framework\TestCase;
use Suphle\Contracts\Response\OpenApiRenderer;
use Suphle\Response\Traits\OpenApiRendererTrait;
use Suphle\Request\PayloadStorage;

class OpenApiRendererInterfaceTest extends TestCase
{
    public function test_trait_provides_default_implementations()
    {
        $renderer = new class implements OpenApiRenderer {
            use OpenApiRendererTrait;
        };

        $this->assertEquals(PayloadStorage::HTML_HEADER_VALUE, $renderer::getContentType());
        $this->assertEquals(200, $renderer::getStatusCode());
        $this->assertIsArray($renderer::getResponseSchema());
        $this->assertStringContainsString('response', $renderer::getDescription());
    }

    public function test_trait_generates_description_from_class_name()
    {
        $renderer = new class implements OpenApiRenderer {
            use OpenApiRendererTrait;
        };

        $description = $renderer::getDescription();
        $this->assertStringContainsString('response', $description);
        $this->assertNotEmpty($description);
    }

    public function test_custom_renderer_can_override_defaults()
    {
        $renderer = new class implements OpenApiRenderer {
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

        $this->assertEquals('application/custom', $renderer::getContentType());
        $this->assertEquals(201, $renderer::getStatusCode());
        $this->assertEquals('object', $renderer::getResponseSchema()['type']);
        $this->assertEquals('Custom API response', $renderer::getDescription());
    }
} 