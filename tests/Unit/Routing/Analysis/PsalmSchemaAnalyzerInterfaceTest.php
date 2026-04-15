<?php

namespace Suphle\Tests\Unit\Routing\Analysis;

use Suphle\Routing\Analysis\{PsalmSchemaAnalyzer, RendererAnalyzerRegistry};
use Suphle\Contracts\Config\Router as RouterConfig;
use Suphle\Contracts\Flows\FlowHydrator;
use Suphle\Request\PayloadStorage;
use Suphle\Testing\TestTypes\IsolatedComponentTest;
use Suphle\Tests\Integration\Generic\CommonBinds;
use ReflectionMethod;

class PsalmSchemaAnalyzerInterfaceTest extends IsolatedComponentTest
{
    use CommonBinds;

    protected function concreteBinds(): array
    {
        return array_merge(parent::concreteBinds(), [
            FlowHydrator::class => $this->positiveDouble(FlowHydrator::class),
            RendererAnalyzerRegistry::class => $this->container->getClass(RendererAnalyzerRegistry::class)
        ]);
    }

    public function test_interface_renderer_content_type_detection()
    {
        // Given
        $analyzer = $this->container->getClass(PsalmSchemaAnalyzer::class);
        $customRenderer = ;

        // When
        $contentType = $analyzer->getContentTypeForRenderer(get_class($customRenderer));

        // Then
        $this->assertEquals('application/custom', $contentType);
    }

    public function test_interface_renderer_status_code_detection()
    {
        // Given
        $analyzer = $this->container->getClass(PsalmSchemaAnalyzer::class);
        $customRenderer = ; // what's this nonsense? use regular suphle renderers

        // When
        $statusCode = $analyzer->getStatusCodeForRenderer(get_class($customRenderer));

        // Then
        $this->assertEquals(201, $statusCode);
    }

    public function test_interface_renderer_schema_detection()
    {
        // Given
        $analyzer = $this->container->getClass(PsalmSchemaAnalyzer::class);
        $customRenderer = ;

        $method = new ReflectionMethod($customRenderer, '__construct');

        // When
        $schema = $analyzer->analyzeRendererSchemaWithPsalm(get_class($customRenderer), $method);
        
        // Then
        $this->assertIsArray($schema);
        $this->assertEquals('object', $schema['type']);
        $this->assertArrayHasKey('properties', $schema);
    }

    public function test_fallback_to_legacy_analysis_for_non_interface_renderers()
    {
        // Given
        $analyzer = $this->container->getClass(PsalmSchemaAnalyzer::class);
        $legacyRenderer = new class {
            public function render(): string
            {
                return 'legacy';
            }
        };

        // When
        $contentType = $analyzer->getContentTypeForRenderer(get_class($legacyRenderer));

        // Then
        $this->assertEquals(PayloadStorage::HTML_HEADER_VALUE, $contentType); // Default fallback
    }

    public function test_interface_takes_precedence_over_legacy_mapping()
    {
        // Given
        $analyzer = $this->container->getClass(PsalmSchemaAnalyzer::class);
        $customRenderer = ;

        // When
        $contentType = $analyzer->getContentTypeForRenderer(get_class($customRenderer));
        $statusCode = $analyzer->getOpenApiStatusCodeForRenderer(get_class($customRenderer));

        // Then
        $this->assertEquals('application/override', $contentType);
        $this->assertEquals(418, $statusCode);
    }
}
 