<?php

namespace Suphle\Tests\Integration\Routing\Documentation;

use Suphle\Contracts\{Config\Router as RouterConfig, Response\OpenApiRenderer};
use Suphle\Testing\{TestTypes\ModuleLevelTest, Proxies\WriteOnlyContainer};
use Suphle\Tests\Mocks\Modules\ModuleOne\{Meta\ModuleOneDescriptor, Coordinators\TestCoordinator};
use Suphle\Response\Traits\OpenApiRendererTrait;
use Suphle\Request\PayloadStorage;

class OpenApiRendererIntegrationTest extends ModuleLevelTest
{
    protected function getModules(): array
    {
        return [
            $this->replicateModule(ModuleOneDescriptor::class, function (WriteOnlyContainer $container) {
                $container->replaceWithMock(RouterConfig::class, RouterConfig::class, [
                    "getCoordinatorClassesToScan" => [TestCoordinator::class]
                ]);
            })
        ];
    }

    public function test_openapi_spec_includes_interface_renderer_metadata()
    {
        $response = $this->get('/api-docs/json');
        
        $this->assertNotNull($response);
        $this->assertInstanceOf(\Suphle\Contracts\Presentation\BaseRenderer::class, $response);
        
        // The response should contain JSON data
        $content = $response->render();
        $spec = json_decode($content, true);
        
        $this->assertIsArray($spec);
        $this->assertArrayHasKey('paths', $spec);
        
        // Check that the spec contains our test routes
        $this->assertArrayHasKey('/api-docs', $spec['paths']);
        $this->assertArrayHasKey('/api-docs/json', $spec['paths']);
    }

    public function test_custom_renderer_auto_discovery()
    {
        // Create a test coordinator with custom renderer
        $testCoordinator = new class extends TestCoordinator {
            public function testCustomRoute(): CustomRenderer
            {
                return new CustomRenderer();
            }
        };
        
        // Update the module to use our test coordinator
        $this->updateModule(ModuleOneDescriptor::class, function (WriteOnlyContainer $container) use ($testCoordinator) {
            $container->replaceWithMock(RouterConfig::class, RouterConfig::class, [
                "getCoordinatorClassesToScan" => [get_class($testCoordinator)]
            ]);
        });
        
        $response = $this->get('/api-docs/json');
        
        $this->assertNotNull($response);
        $content = $response->render();
        $spec = json_decode($content, true);
        
        // The spec should be generated successfully
        $this->assertIsArray($spec);
        $this->assertArrayHasKey('paths', $spec);
    }

    public function test_interface_renderer_with_validation_rules()
    {
        // Create a test coordinator with validation rules
        $testCoordinator = new class extends TestCoordinator {
            public function testValidatedRoute(): \Suphle\Response\Format\Json
            {
                return new \Suphle\Response\Format\Json(['validated' => true]);
            }
        };
        
        // Update the module to use our test coordinator
        $this->updateModule(ModuleOneDescriptor::class, function (WriteOnlyContainer $container) use ($testCoordinator) {
            $container->replaceWithMock(RouterConfig::class, RouterConfig::class, [
                "getCoordinatorClassesToScan" => [get_class($testCoordinator)]
            ]);
        });
        
        $response = $this->get('/api-docs/json');
        
        $this->assertNotNull($response);
        $content = $response->render();
        $spec = json_decode($content, true);
        
        // The spec should be generated successfully
        $this->assertIsArray($spec);
        $this->assertArrayHasKey('paths', $spec);
    }

    public function test_interface_renderer_fallback_behavior()
    {
        // Create a test coordinator with legacy renderer
        $testCoordinator = new class extends TestCoordinator {
            public function testLegacyRoute(): object
            {
                return new class {
                    public function render(): string
                    {
                        return 'legacy';
                    }
                };
            }
        };
        
        // Update the module to use our test coordinator
        $this->updateModule(ModuleOneDescriptor::class, function (WriteOnlyContainer $container) use ($testCoordinator) {
            $container->replaceWithMock(RouterConfig::class, RouterConfig::class, [
                "getCoordinatorClassesToScan" => [get_class($testCoordinator)]
            ]);
        });
        
        $response = $this->get('/api-docs/json');
        
        $this->assertNotNull($response);
        $content = $response->render();
        $spec = json_decode($content, true);
        
        // The spec should be generated successfully even with legacy renderer
        $this->assertIsArray($spec);
        $this->assertArrayHasKey('paths', $spec);
    }
}

// Custom renderer for testing
class CustomRenderer implements OpenApiRenderer
{
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
                'custom_field' => ['type' => 'string'],
                'custom_number' => ['type' => 'integer']
            ]
        ];
    }

    public static function getDescription(): string
    {
        return 'Custom test renderer response';
    }

    public function render(): string
    {
        return json_encode(['custom_field' => 'test', 'custom_number' => 42]);
    }
} 