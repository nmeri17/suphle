<?php

namespace Suphle\Tests\Unit\Response\Format;

use PHPUnit\Framework\TestCase;
use Suphle\Response\Format\{Json, Markup, Redirect, Reload, LocalFileDownload};
use Suphle\Adapters\Presentation\Hotwire\Formats\{BaseHotwireStream, RedirectHotwireStream, ReloadHotwireStream};
use Suphle\Request\PayloadStorage;

class OpenApiRendererImplementationTest extends TestCase
{
    public function test_json_renderer_interface_implementation()
    {
        // Test that Json renderer implements the interface correctly
        $this->assertTrue(is_subclass_of(Json::class, \Suphle\Contracts\Response\OpenApiRenderer::class));
        
        // Test content type matches actual renderer behavior
        $this->assertEquals(PayloadStorage::JSON_HEADER_VALUE, Json::getContentType());
        
        // Test response schema is appropriate for JSON
        $schema = Json::getResponseSchema();
        $this->assertEquals('object', $schema['type']);
        $this->assertStringContainsString('JSON', Json::getDescription());
    }

    public function test_markup_renderer_interface_implementation()
    {
        $this->assertTrue(is_subclass_of(Markup::class, \Suphle\Contracts\Response\OpenApiRenderer::class));
        
        // Test content type matches actual renderer behavior
        $this->assertEquals(PayloadStorage::HTML_HEADER_VALUE, Markup::getContentType());
        
        // Test response schema is appropriate for HTML
        $schema = Markup::getResponseSchema();
        $this->assertEquals('string', $schema['type']);
        $this->assertEquals('html', $schema['format']);
        $this->assertStringContainsString('HTML', Markup::getDescription());
    }

    public function test_redirect_renderer_interface_implementation()
    {
        $this->assertTrue(is_subclass_of(Redirect::class, \Suphle\Contracts\Response\OpenApiRenderer::class));
        
        // Test status code matches actual redirect behavior
        $this->assertEquals(Redirect::STATUS_CODE, Redirect::getStatusCode());
        
        // Test response schema includes Location header
        $schema = Redirect::getResponseSchema();
        $this->assertArrayHasKey('headers', $schema);
        $this->assertArrayHasKey('Location', $schema['headers']);
        $this->assertStringContainsString('redirect', Redirect::getDescription());
    }

    public function test_reload_renderer_interface_implementation()
    {
        $this->assertTrue(is_subclass_of(Reload::class, \Suphle\Contracts\Response\OpenApiRenderer::class));
        
        // Test status code matches actual reload behavior
        $this->assertEquals(Reload::STATUS_CODE, Reload::getStatusCode());
        
        // Test response schema is appropriate for reload
        $schema = Reload::getResponseSchema();
        $this->assertEquals('string', $schema['type']);
        $this->assertStringContainsString('reload', Reload::getDescription());
    }

    public function test_local_file_download_renderer_interface_implementation()
    {
        $this->assertTrue(is_subclass_of(LocalFileDownload::class, \Suphle\Contracts\Response\OpenApiRenderer::class));
        
        // Test content type is appropriate for file downloads
        $this->assertEquals('application/octet-stream', LocalFileDownload::getContentType());
        
        // Test response schema includes download headers
        $schema = LocalFileDownload::getResponseSchema();
        $this->assertEquals('binary', $schema['format']);
        $this->assertArrayHasKey('headers', $schema);
        $this->assertArrayHasKey('Content-Disposition', $schema['headers']);
        $this->assertArrayHasKey('Content-Length', $schema['headers']);
        $this->assertStringContainsString('download', LocalFileDownload::getDescription());
    }

    public function test_hotwire_stream_renderers_interface_implementation()
    {
        // Test base Hotwire stream
        $this->assertTrue(is_subclass_of(BaseHotwireStream::class, \Suphle\Contracts\Response\OpenApiRenderer::class));
        $this->assertEquals(BaseHotwireStream::TURBO_INDICATOR, BaseHotwireStream::getContentType());
        
        // Test redirect Hotwire stream
        $this->assertTrue(is_subclass_of(RedirectHotwireStream::class, \Suphle\Contracts\Response\OpenApiRenderer::class));
        $this->assertEquals(RedirectHotwireStream::STATUS_CODE, RedirectHotwireStream::getStatusCode());
        
        // Test reload Hotwire stream
        $this->assertTrue(is_subclass_of(ReloadHotwireStream::class, \Suphle\Contracts\Response\OpenApiRenderer::class));
        
        // All Hotwire streams should have appropriate schemas
        $this->assertEquals('string', BaseHotwireStream::getResponseSchema()['type']);
        $this->assertEquals('html', BaseHotwireStream::getResponseSchema()['format']);
        $this->assertStringContainsString('Turbo', BaseHotwireStream::getDescription());
    }

    public function test_interface_methods_are_static()
    {
        // Test that all interface methods are static and accessible
        $renderers = [
            Json::class,
            Markup::class,
            Redirect::class,
            Reload::class,
            LocalFileDownload::class,
            BaseHotwireStream::class,
            RedirectHotwireStream::class,
            ReloadHotwireStream::class
        ];

        foreach ($renderers as $rendererClass) {
            $this->assertIsString($rendererClass::getContentType());
            $this->assertIsInt($rendererClass::getStatusCode());
            $this->assertIsArray($rendererClass::getResponseSchema());
            $this->assertIsString($rendererClass::getDescription());
        }
    }

    public function test_response_schemas_are_valid_openapi()
    {
        $renderers = [
            Json::class,
            Markup::class,
            Redirect::class,
            Reload::class,
            LocalFileDownload::class,
            BaseHotwireStream::class
        ];

        foreach ($renderers as $rendererClass) {
            $schema = $rendererClass::getResponseSchema();
            
            // Basic OpenAPI schema validation
            $this->assertArrayHasKey('type', $schema);
            $this->assertContains($schema['type'], ['string', 'object', 'array']);
            
            if (isset($schema['properties'])) {
                $this->assertIsArray($schema['properties']);
            }
            
            if (isset($schema['headers'])) {
                $this->assertIsArray($schema['headers']);
                foreach ($schema['headers'] as $header) {
                    $this->assertArrayHasKey('description', $header);
                    $this->assertArrayHasKey('schema', $header);
                }
            }
        }
    }

    public function test_content_types_are_valid_mime_types()
    {
        $renderers = [
            Json::class,
            Markup::class,
            Redirect::class,
            Reload::class,
            LocalFileDownload::class,
            BaseHotwireStream::class
        ];

        foreach ($renderers as $rendererClass) {
            $contentType = $rendererClass::getContentType();
            
            // Basic MIME type validation
            $this->assertMatchesRegularExpression('/^[a-z]+\/[a-z0-9\-_\+\.]+$|^text\/vnd\.[a-z0-9\-_\+\.]+$/', $contentType);
        }
    }

    public function test_status_codes_are_valid_http_codes()
    {
        $renderers = [
            Json::class,
            Markup::class,
            Redirect::class,
            Reload::class,
            LocalFileDownload::class,
            BaseHotwireStream::class
        ];

        foreach ($renderers as $rendererClass) {
            $statusCode = $rendererClass::getStatusCode();
            
            // Valid HTTP status code range
            $this->assertGreaterThanOrEqual(100, $statusCode);
            $this->assertLessThanOrEqual(599, $statusCode);
        }
    }

    public function test_descriptions_are_meaningful()
    {
        $renderers = [
            Json::class,
            Markup::class,
            Redirect::class,
            Reload::class,
            LocalFileDownload::class,
            BaseHotwireStream::class
        ];

        foreach ($renderers as $rendererClass) {
            $description = $rendererClass::getDescription();
            
            // Descriptions should be meaningful
            $this->assertGreaterThan(5, strlen($description));
            $this->assertStringContainsString('response', $description);
            $this->assertNotEquals('response', $description); // Should be more specific
        }
    }
} 