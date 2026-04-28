<?php
namespace Suphle\Tests\Unit\Routing\Documentation;

use Suphle\Routing\{AttributeRouteScanner, Analysis\PsalmSchemaAnalyzer, Documentation\OpenApiGeneratorService};

use Suphle\Contracts\Database\ModelSchemaDetector;
use Suphle\Testing\TestType\IsolatedComponentTest;
use Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators\BaseCoordinator;

class OpenApiGeneratorServiceUnitTest extends IsolatedComponentTest
{
    private OpenApiGeneratorService $sut;

    private PsalmSchemaAnalyzer $analyzer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->analyzer = $this->getContainer()->getClass(PsalmSchemaAnalyzer::class);

        $this->sut = $this->replaceConstructorArguments(OpenApiGeneratorService::class);
    }

    /**
     * Proves SUT correctly transforms path placeholders like {id} 
     * into OpenAPI parameter objects.
     */
    public function test_transform_placeholders_to_path_parameters()
    {
        // given
        $routeData = $this->getAnalyzedRoute('multiPlaceholders');

        $this->massProvide([
            AttributeRouteScanner::class => $this->positiveDouble(AttributeRouteScanner::class, [
                "scanModulesByPath" => [$routeData]
            ])
        ]);

        // when
        $spec = $this->sut->generateOpenApiSpec("http://localhost");

        // then
        $operation = $spec['paths']['/segment/{id}/segment/{id2}']['get'];
        
        $this->assertCount(2, $operation['parameters']);
        $this->assertEquals('id', $operation['parameters'][0]['name']);
        $this->assertEquals('path', $operation['parameters'][0]['in']);
        $this->assertTrue($operation['parameters'][0]['required']);
    }

    /**
     * Proves SUT handles Method Injection vs Query Parameters.
     * In 'incorrectActionInjection', $payload is a Reader (Payload), 
     * but $aRequires is a service. Neither should appear in 'query' params.
     */
    public function test_filters_out_payload_readers_and_services_from_parameters()
    {
        // given
        $routeData = $this->getAnalyzedRoute('incorrectActionInjection');

        $this->massProvide([
            AttributeRouteScanner::class => $this->positiveDouble(AttributeRouteScanner::class, [
                "scanModulesByPath" => [$routeData]
            ])
        ]);

        // when
        $spec = $this->sut->generateOpenApiSpec("http://localhost");

        // then
        $operation = $spec['paths']['/incorrect-action']['post'];
        
        // Assert that services and readers aren't leaked as query parameters
        foreach ($operation['parameters'] as $param) {
            $this->assertNotEquals('payload', $param['name']);
            $this->assertNotEquals('aRequires', $param['name']);
        }
    }

    /**
     * Proves SUT generates valid OperationIds based on Handler names
     */
    public function test_generates_valid_operation_ids()
    {
        // given
        $routeData = $this->getAnalyzedRoute('indexHandler');

        $this->massProvide([
            AttributeRouteScanner::class => $this->positiveDouble(AttributeRouteScanner::class, [
                "scanModulesByPath" => [$routeData]
            ])
        ]);

        // when
        $spec = $this->sut->generateOpenApiSpec("http://localhost");

        // then
        $operation = $spec['paths']['/']['get'];
        $this->assertEquals('get_indexHandler', $operation['operationId']);
    }

    /**
     * Proves SUT correctly maps the Base URL to the servers block
     */
    public function test_maps_server_url()
    {
        // when
        $spec = $this->sut->generateOpenApiSpec("https://api.suphle.io");

        // then
        $this->assertEquals("https://api.suphle.io", $spec['servers'][0]['url']);
    }

    /**
     * Helper to get real metadata for a specific method from the sample controller
     */
    private function getAnalyzedRoute(string $methodName): array
    {
        $all = $this->analyzer->analyzeCoordinator(BaseCoordinator::class, "ModuleOne");
        
        return array_values(array_filter($all, fn($r) => $r['handler'] === $methodName))[0];
    }
}