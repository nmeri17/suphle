<?php

namespace Suphle\Tests\Unit\Routing\Documentation;

use Suphle\Routing\{AttributeRouteScanner, Documentation\OpenApiGeneratorService, Analysis\PsalmSchemaAnalyzer};

use Suphle\Contracts\PsalmCodebase;

use Suphle\Testing\TestTypes\IsolatedComponentTest;

use Suphle\Tests\Integration\Generic\CommonBinds;

use Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators\BaseCoordinator;

/*Your service has a bunch of regex methods like buildSchemaFromRules that look at validation strings (e.g., "required|integer|min:5|max:100") and convert them into arrays like ['type' => 'integer', 'minimum' => 5].

Instead of booting up the entire framework, this file isolates just those string conversion methods. It feeds them dummy validation strings to make sure the regex doesn't break if someone modifies the string parsing rules later.*/
class OpenApiGeneratorServiceTest extends IsolatedComponentTest
{
    use CommonBinds;
    /**
     * Instantiates the live service with cleanly auto-hydrated dependencies.
     */
    private function getSut(): OpenApiGeneratorService
    {
        return $this->replaceConstructorArguments(OpenApiGeneratorService::class);
    }

    /**
     * Helper to get real metadata for a specific method from the sample coordinator
     */
    private function getAnalyzedRoute(string $methodName): array
    {
        $coordinatorClass = BaseCoordinator::class;

        $container = $this->getContainer();

        $container->getClass(PsalmCodebase::class)->scanSingleClass($coordinatorClass);
        
        $all = $container->getClass(PsalmSchemaAnalyzer::class)
        ->analyzeCoordinator($coordinatorClass, "ModuleOne");
        
        return array_values(array_filter($all, fn($r) => $r['handler'] === $methodName))[0];
    }

    /**
     * Proves SUT correctly transforms path placeholders like {id} 
     * into OpenAPI parameter objects.
     */
    public function test_transform_placeholders_to_path_parameters()
    {
        // Given
        $routeData = $this->getAnalyzedRoute('multiPlaceholders');

        $this->massProvide([
            AttributeRouteScanner::class => $this->positiveDouble(AttributeRouteScanner::class, [
                "scanModulesByPath" => [$routeData]
            ])
        ]);

        // When
        $spec = $this->getSut()->generateOpenApiSpec("http://localhost");

        // Then
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
        // Given
        $routeData = $this->getAnalyzedRoute('incorrectActionInjection');

        $this->massProvide([
            AttributeRouteScanner::class => $this->positiveDouble(AttributeRouteScanner::class, [
                "scanModulesByPath" => [$routeData]
            ])
        ]);

        // When
        $spec = $this->getSut()->generateOpenApiSpec("http://localhost");

        // Then
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
        // Given
        $routeData = $this->getAnalyzedRoute('indexHandler');

        $this->massProvide([
            AttributeRouteScanner::class => $this->positiveDouble(AttributeRouteScanner::class, [
                "scanModulesByPath" => [$routeData]
            ])
        ]);

        // When
        $spec = $this->getSut()->generateOpenApiSpec("http://localhost");

        // Then
        $operation = $spec['paths']['/']['get'];
        $this->assertEquals('get_indexHandler', $operation['operationId']);
    }

    /**
     * Proves SUT correctly maps the Base URL to the servers block
     */
    public function test_maps_server_url()
    {
        // When
        $spec = $this->getSut()->generateOpenApiSpec("https://api.suphle.io");

        // Then
        $this->assertEquals("https://api.suphle.io", $spec['servers'][0]['url']);
    }

    /**
     * Verifies complex validation rule strings translate to valid OpenAPI parameters.
     */
    public function test_validation_rules_string_parsing_logic()
    {
        $this->dataProvider([
            $this->validationRuleProvider(...)
        ], function (string $rules, array $expectedSchema) {
            $sut = $this->getSut();
            $this->assertEquals($expectedSchema, $sut->buildSchemaFromRules($rules));
        });
    }

    public function validationRuleProvider(): array
    {
        return [
            "Integer with bounds" => [
                "required|integer|min:5|max:100",
                ["type" => "integer", "minimum" => 5, "maximum" => 100]
            ],
            "String bounds matching lengths" => [
                "string|min:10|max:50",
                ["type" => "string", "minLength" => 10, "maxLength" => 50]
            ],
            "Enum string inside items array parsing" => [
                "array|in:active,inactive,pending",
                [
                    "type" => "array",
                    "items" => [
                        "type" => "object",
                        "enum" => ["active", "inactive", "pending"]
                    ]
                ]
            ],
            "Nullable fields validation" => [
                "nullable|numeric",
                ["type" => "number", "nullable" => true]
            ]
        ];
    }

    /**
     * Verifies native PHP types map flawlessly onto generic OpenAPI primitives.
     */
    public function test_php_to_openapi_type_mapping()
    {
        $sut = $this->getSut();
        
        $this->assertSame('integer', $sut->mapPhpTypeToOpenApi('int'));
        $this->assertSame('number', $sut->mapPhpTypeToOpenApi('float'));
        $this->assertSame('boolean', $sut->mapPhpTypeToOpenApi('bool'));
        $this->assertSame('string', $sut->mapPhpTypeToOpenApi('unknown-class-fallback'));
    }
}