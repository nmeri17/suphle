<?php

namespace Suphle\Tests\Unit\Response\Format;

use Suphle\Testing\TestTypes\IsolatedComponentTest;
use Suphle\Routing\Analysis\PsalmSchemaAnalyzer;
use Suphle\Response\Format\{Json, Markup, Redirect, Reload, LocalFileDownload};
use Suphle\Adapters\Presentation\Hotwire\Formats\BaseHotwireStream;
use Suphle\Tests\Integration\Generic\CommonBinds;

/*sut passes renderer names into PsalmSchemaAnalyzer to figure out the schema. This verifies that your analyzer successfully recognizes each response type and outputs valid OpenAPI boilerplate for them.*/
class OpenApiRendererImplementationTest extends IsolatedComponentTest
{
    use CommonBinds;
    
    public function test_markup_renderer_schema_resolution()
    {
        $analyzer = $this->getContainer()->getClass(PsalmSchemaAnalyzer::class);
        $schema = $analyzer->getStandardFormatSchema(Markup::class);
        
        $this->assertIsArray($schema);var_dump($schema);
        $this->assertArrayHasKey('type', $schema);
        $this->assertEquals('string', $schema['type']);
        $this->assertEquals('html', $schema['format'] ?? null);
    }

    public function test_hotwire_stream_renderer_schema_resolution()
    {
        $analyzer = $this->getContainer()->getClass(PsalmSchemaAnalyzer::class);
        $schema = $analyzer->getStandardFormatSchema(BaseHotwireStream::class);
        
        $this->assertIsArray($schema);
        $this->assertEquals('string', $schema['type']);
        
        if (isset($schema['contentMediaType'])) {
            $this->assertMatchesRegularExpression('/^[a-z]+\/[a-z0-9\-_\+\.]+$/', $schema['contentMediaType']); // match mime type eg text/html, application/json
        }
    }

    public function test_all_core_renderers_produce_valid_openapi_specifications()
    {
        $analyzer = $this->getContainer()->getClass(PsalmSchemaAnalyzer::class);

        $renderers = [
            Json::class,
            Markup::class,
            Redirect::class,
            Reload::class,
            LocalFileDownload::class,
            BaseHotwireStream::class
        ];

        foreach ($renderers as $rendererClass) {
            $schema = $analyzer->getStandardFormatSchema($rendererClass);
            
            $this->assertIsArray($schema);
            $this->assertArrayHasKey('type', $schema);
            $this->assertContains($schema['type'], ['string', 'object', 'array']);

            $statusCode = $rendererClass::STATUS_CODE;
            $this->assertGreaterThanOrEqual(100, $statusCode);
            $this->assertLessThanOrEqual(599, $statusCode);
        }
    }
}