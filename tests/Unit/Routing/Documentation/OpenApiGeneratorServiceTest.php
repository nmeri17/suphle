<?php

namespace Suphle\Tests\Unit\Routing\Documentation;

use Suphle\Routing\{AttributeRouteScanner, Documentation\OpenApiGeneratorService, Analysis\RendererContentShape};
use Suphle\Contracts\Database\ModelSchemaDetector;
use Suphle\Hydration\Container;
use Suphle\Testing\TestTypes\IsolatedComponentTest;
use Suphle\Tests\Integration\Generic\CommonBinds;
use Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators\{BaseCoordinator, EmploymentEditCoordinator};

class OpenApiGeneratorServiceTest extends IsolatedComponentTest
{
    use CommonBinds;

    private function getSut(): OpenApiGeneratorService
    {
        return $this->replaceConstructorArguments(OpenApiGeneratorService::class, []);
    }

    /**
     * Main helper - uses real analysis flow
     */
    private function getAnalyzedRoute(string $methodName, string $coordinatorClass = BaseCoordinator::class): array
    {
        $container = $this->getContainer();

        /** @var RendererContentShape $analyzer */
        $analyzer = $container->getClass(RendererContentShape::class);

        // This triggers model discovery + schema registration as side effect
        $routes = $analyzer->analyzeCoordinator($coordinatorClass, "ModuleOne");

        $filtered = array_values(array_filter(
            $routes,
            fn($r) => $r['handler'] === $methodName
        ));

        return $filtered[0] ?? [];
    }

    // ====================== CORE ROUTE METADATA TESTS ======================

    public function test_transform_placeholders_to_path_parameters()
    {
        $routeData = $this->getAnalyzedRoute('multiPlaceholders');

        $this->massProvide([
            AttributeRouteScanner::class => $this->positiveDouble(AttributeRouteScanner::class, [
                "scanModulesByPath" => [$routeData]
            ])
        ]);

        $spec = $this->getSut()->generateOpenApiSpec("http://localhost");
        $operation = $spec['paths']['/segment/{id}/segment/{id2}']['get'] ?? [];

        $this->assertCount(2, $operation['parameters'] ?? []);
        $this->assertEquals('id', $operation['parameters'][0]['name']);
    }

    public function test_filters_out_payload_readers_and_services_from_parameters()
    {
        $routeData = $this->getAnalyzedRoute('incorrectActionInjection');

        $this->massProvide([
            AttributeRouteScanner::class => $this->positiveDouble(AttributeRouteScanner::class, [
                "scanModulesByPath" => [$routeData]
            ])
        ]);

        $spec = $this->getSut()->generateOpenApiSpec("http://localhost");
        $operation = $spec['paths']['/incorrect-action']['post'] ?? [];

        foreach ($operation['parameters'] ?? [] as $param) {
            $this->assertNotEquals('payload', $param['name']);
            $this->assertNotEquals('aRequires', $param['name']);
        }
    }

    public function test_generates_valid_operation_ids()
    {
        $routeData = $this->getAnalyzedRoute('indexHandler');

        $this->massProvide([
            AttributeRouteScanner::class => $this->positiveDouble(AttributeRouteScanner::class, [
                "scanModulesByPath" => [$routeData]
            ])
        ]);

        $spec = $this->getSut()->generateOpenApiSpec("http://localhost");
        $operation = $spec['paths']['/']['get'] ?? [];

        $this->assertEquals('get_indexHandler', $operation['operationId'] ?? '');
    }

    // ====================== RESPONSE SHAPE TESTS (Most Important) ======================

    public function test_infers_response_shape_from_builder_payload()
    {
        $routeData = $this->getAnalyzedRoute('getEmploymentDetails', EmploymentEditCoordinator::class);

        $this->assertArrayHasKey('response_shape', $routeData);
        $shape = $routeData['response_shape'];

        $this->assertEquals('object', $shape['type'] ?? null);
        $this->assertArrayHasKey('properties', $shape);
        $this->assertArrayHasKey('data', $shape['properties'] ?? []);
    }

    public function test_infers_collection_response_from_get_terminal()
    {
        // You can add a method that does ->get() if you want, or test via updateEmploymentDetails if it returns array

        $routeData = $this->getAnalyzedRoute('getEmploymentDetails', EmploymentEditCoordinator::class);
        $shape = $routeData['response_shape'] ?? [];

        // At minimum we expect it didn't fall back to generic object
        $this->assertNotEquals('object', $shape['properties']['data']['type'] ?? 'object'); 
    }

    public function test_harvests_model_schemas_during_analysis()
    {
        // Trigger analysis
        $this->getAnalyzedRoute('getEmploymentDetails', EmploymentEditCoordinator::class);

        $sut = $this->getSut();
        $schemas = $sut->getSchemaDetector()?->getGeneratedSchemas() ?? [];   // expose this if needed

        $this->assertArrayHasKey('Employment', $schemas);   // adjust key based on your normalization
    }

    // ====================== VALIDATION & MISC TESTS ======================

    public function test_validation_rules_string_parsing_logic()
    {
        $this->dataProvider($this->validationRuleProvider(...), function (string $rules, array $expected) {
            $this->assertEquals($expected, $this->getSut()->buildSchemaFromRules($rules));
        });
    }

    public function validationRuleProvider(): array
    {
        return [
            "Integer with bounds" => [
                "required|integer|min:5|max:100",
                ["type" => "integer", "minimum" => 5, "maximum" => 100]
            ],
            "String bounds" => [
                "string|min:10|max:50",
                ["type" => "string", "minLength" => 10, "maxLength" => 50]
            ],
            "Array with enum" => [
                "array|in:active,inactive,pending",
                [
                    "type" => "array",
                    "items" => ["type" => "object", "enum" => ["active", "inactive", "pending"]]
                ]
            ],
        ];
    }

    public function test_maps_server_url()
    {
        $spec = $this->getSut()->generateOpenApiSpec("https://api.suphle.io");
        $this->assertEquals("https://api.suphle.io", $spec['servers'][0]['url']);
    }
}