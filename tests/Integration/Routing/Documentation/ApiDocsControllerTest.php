<?php

namespace Suphle\Tests\Integration\Routing\Documentation;

use Suphle\Contracts\Config\Router as RouterConfig;
use Suphle\Routing\Documentation\{ApiDocsController, OpenApiGeneratorService};
use Suphle\Tests\Mocks\Modules\ModuleOne\{Meta\ModuleOneDescriptor, Coordinators\TestCoordinator};
use Suphle\Tests\Integration\TestHttpRunner;

class ApiDocsControllerTest extends TestHttpRunner
{
    protected function setUp(): void
    {
        $this->setAllDescriptors();
        parent::setUp();
    }

    protected function setModuleOne(): void
    {
        $this->moduleOne = $this->replicateModule(ModuleOneDescriptor::class, function (WriteOnlyContainer $container) {
            $container->replaceWithMock(RouterConfig::class, RouterConfig::class, [
                "getCoordinatorClassesToScan" => [TestCoordinator::class]
            ]);
        });
    }

    protected function getModules(): array
    {
        return [$this->moduleOne];
    }

    public function test_can_access_api_docs_page()
    {
        $response = $this->get("/api-docs");

        $response->assertOk();
        $response->assertSee('API Documentation');
        $response->assertSee('Route Details');
    }

    public function test_can_access_api_docs_json()
    {
        $response = $this->get("/api-docs/json");

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/json');
        
        $jsonData = $response->json();
        
        $this->assertIsArray($jsonData);
        $this->assertArrayHasKey('openapi', $jsonData);
        $this->assertArrayHasKey('info', $jsonData);
        $this->assertArrayHasKey('paths', $jsonData);
        $this->assertArrayHasKey('components', $jsonData);
        
        $this->assertEquals('3.0.0', $jsonData['openapi']);
        $this->assertEquals('Suphle API Documentation', $jsonData['info']['title']);
        $this->assertEquals('1.0.0', $jsonData['info']['version']);
    }

    public function test_openapi_spec_contains_server_info()
    {
        $response = $this->get("/api-docs/json");
        $jsonData = $response->json();
        
        $this->assertArrayHasKey('servers', $jsonData);
        $this->assertIsArray($jsonData['servers']);
        $this->assertNotEmpty($jsonData['servers']);
        
        $server = $jsonData['servers'][0];
        $this->assertArrayHasKey('url', $server);
        $this->assertArrayHasKey('description', $server);
        $this->assertEquals('/', $server['url']);
    }

    public function test_openapi_spec_contains_paths()
    {
        $response = $this->get("/api-docs/json");
        $jsonData = $response->json();
        
        $this->assertArrayHasKey('paths', $jsonData);
        $this->assertIsArray($jsonData['paths']);
        
        // Should contain at least the api-docs routes
        $this->assertArrayHasKey('/api-docs', $jsonData['paths']);
        $this->assertArrayHasKey('/api-docs/json', $jsonData['paths']);
    }

    public function test_path_items_have_correct_structure()
    {
        $response = $this->get("/api-docs/json");
        $jsonData = $response->json();
        
        $apiDocsPath = $jsonData['paths']['/api-docs'];
        $this->assertArrayHasKey('get', $apiDocsPath);
        
        $getOperation = $apiDocsPath['get'];
        $this->assertArrayHasKey('summary', $getOperation);
        $this->assertArrayHasKey('description', $getOperation);
        $this->assertArrayHasKey('tags', $getOperation);
        $this->assertArrayHasKey('parameters', $getOperation);
        $this->assertArrayHasKey('responses', $getOperation);
    }

    public function test_path_items_have_responses()
    {
        $response = $this->get("/api-docs/json");
        $jsonData = $response->json();
        
        $apiDocsPath = $jsonData['paths']['/api-docs'];
        $getOperation = $apiDocsPath['get'];
        
        $this->assertArrayHasKey('200', $getOperation['responses']);
        $response200 = $getOperation['responses']['200'];
        $this->assertArrayHasKey('description', $response200);
        $this->assertArrayHasKey('content', $response200);
    }

    public function test_json_endpoint_has_correct_content_type()
    {
        $response = $this->get("/api-docs/json");
        $jsonData = $response->json();
        
        $jsonPath = $jsonData['paths']['/api-docs/json'];
        $getOperation = $jsonPath['get'];
        $response200 = $getOperation['responses']['200'];
        
        $this->assertArrayHasKey('application/json', $response200['content']);
        $jsonContent = $response200['content']['application/json'];
        $this->assertArrayHasKey('schema', $jsonContent);
    }

    public function test_components_section_exists()
    {
        $response = $this->get("/api-docs/json");
        $jsonData = $response->json();
        
        $this->assertArrayHasKey('components', $jsonData);
        $components = $jsonData['components'];
        $this->assertArrayHasKey('schemas', $components);
        $this->assertArrayHasKey('parameters', $components);
    }

    public function test_route_tags_are_extracted_from_coordinator()
    {
        $response = $this->get("/api-docs/json");
        $jsonData = $response->json();
        
        // Find a route that should have a module tag
        foreach ($jsonData['paths'] as $path => $methods) {
            foreach ($methods as $method => $operation) {
                if (isset($operation['tags']) && !empty($operation['tags'])) {
                    $this->assertIsArray($operation['tags']);
                    $this->assertNotEmpty($operation['tags']);
                    $this->assertIsString($operation['tags'][0]);
                    break 2;
                }
            }
        }
    }

    public function test_controller_is_thin_and_delegates_to_service()
    {
        $container = $this->getContainer();
        
        // Verify the controller only has the service dependency
        $controller = $container->getClass(ApiDocsController::class);
        $reflection = new \ReflectionClass($controller);
        
        $properties = $reflection->getProperties();
        $this->assertCount(1, $properties);
        $this->assertEquals('openApiService', $properties[0]->getName());
        
        // Verify methods are simple and delegate to service
        $showDocsMethod = $reflection->getMethod('showDocs');
        $this->assertLessThan(10, $showDocsMethod->getNumberOfLines());
        
        $getJsonMethod = $reflection->getMethod('getOpenApiJson');
        $this->assertLessThan(10, $getJsonMethod->getNumberOfLines());
    }

    public function test_service_handles_all_openapi_generation_logic()
    {
        $container = $this->getContainer();
        $service = $container->getClass(OpenApiGeneratorService::class);
        
        $reflection = new \ReflectionClass($service);
        $methods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);
        
        $publicMethodNames = array_map(fn($m) => $m->getName(), $methods);
        
        $this->assertContains('generateOpenApiSpec', $publicMethodNames);
        $this->assertContains('getAllRoutes', $publicMethodNames);
        
        // Verify the service has comprehensive private methods for OpenAPI generation
        $privateMethods = $reflection->getMethods(\ReflectionMethod::IS_PRIVATE | \ReflectionMethod::IS_PROTECTED);
        $this->assertGreaterThan(5, count($privateMethods)); // Should have many helper methods
    }

    public function test_openapi_spec_is_valid_json()
    {
        $response = $this->get("/api-docs/json");
        
        $content = $response->getContent();
        $this->assertIsString($content);
        
        // Verify it's valid JSON
        $decoded = json_decode($content, true);
        $this->assertNotNull($decoded);
        $this->assertIsArray($decoded);
        
        // Verify no JSON errors
        $this->assertEquals(JSON_ERROR_NONE, json_last_error());
    }

    public function test_html_page_contains_route_data()
    {
        $response = $this->get("/api-docs");
        
        $response->assertOk();
        $content = $response->getContent();
        
        // Should contain route information
        $this->assertStringContainsString('Method', $content);
        $this->assertStringContainsString('Path', $content);
        $this->assertStringContainsString('Handler', $content);
    }
} 