<?php
namespace Suphle\Tests\Integration\Routing\Documentation;

use Suphle\Testing\TestTypes\ModuleLevelTest;
use Suphle\Contracts\Config\Router as RouterConfig;
use Suphle\Testing\Proxies\WriteOnlyContainer;
use Suphle\Tests\Mocks\Modules\ModuleOne\Meta\ModuleOneDescriptor;
use Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators\BaseCoordinator;

class OpenApiRendererIntegrationTest extends ModuleLevelTest
{
    protected function getModules(): array
    {
        return [
            $this->replicateModule(
                ModuleOneDescriptor::class,
                function (WriteOnlyContainer $container) {

                    $container->replaceWithMock(
                        RouterConfig::class,
                        RouterConfig::class,
                        [
                            "getCoordinatorClassesToScan" => [
                                BaseCoordinator::class
                            ]
                        ]
                    );
                }
            )
        ];
    }

    private function openApiSpec(): array
    {
        return $this->getJson('/api-docs/json')
            ->assertStatus(200)
            ->json();
    }

    public function test_openapi_root_structure_is_valid()
    {
        $spec = $this->openApiSpec();

        $this->assertSame('3.0.0', $spec['openapi']);

        $this->assertArrayHasKey('paths', $spec);
        $this->assertArrayHasKey('components', $spec);
        $this->assertArrayHasKey('schemas', $spec['components']);
        $this->assertArrayHasKey('servers', $spec);
    }

    public function test_expected_routes_exist()
    {
        $spec = $this->openApiSpec();

        $this->assertArrayHasKey('/api-docs', $spec['paths']);
        $this->assertArrayHasKey('/api-docs/json', $spec['paths']);
    }

    public function test_operations_have_required_fields()
    {
        $spec = $this->openApiSpec();

        foreach ($spec['paths'] as $path => $methods) {
            foreach ($methods as $operation) {

                $this->assertArrayHasKey('summary', $operation);
                $this->assertArrayHasKey('responses', $operation);
                $this->assertArrayHasKey('operationId', $operation);
            }
        }
    }

    public function test_json_endpoint_response_structure()
    {
        $spec = $this->openApiSpec();

        $operation = $spec['paths']['/api-docs/json']['get'] ?? null;

        $this->assertNotNull($operation);

        $this->assertArrayHasKey('responses', $operation);
        $this->assertArrayHasKey('200', $operation['responses']);

        $response = $operation['responses']['200'];

        $this->assertArrayHasKey('content', $response);
        $this->assertArrayHasKey('application/json', $response['content']);
        $this->assertArrayHasKey(
            'schema',
            $response['content']['application/json']
        );
    }

    public function test_request_bodies_follow_validation_rules()
    {
        $spec = $this->openApiSpec();

        foreach ($spec['paths'] as $methods) {
            foreach ($methods as $operation) {

                if (!isset($operation['requestBody'])) {
                    continue;
                }

                $schema = $operation['requestBody']['content']['application/json']['schema'];

                $this->assertSame('object', $schema['type']);
                $this->assertArrayHasKey('properties', $schema);
            }
        }
    }

    public function test_security_scheme_is_registered()
    {
        $spec = $this->openApiSpec();

        $this->assertArrayHasKey('securitySchemes', $spec['components']);
        $this->assertArrayHasKey(
            'bearerAuth',
            $spec['components']['securitySchemes']
        );
    }

    public function test_authenticated_routes_reference_security()
    {
        $spec = $this->openApiSpec();

        foreach ($spec['paths'] as $methods) {
            foreach ($methods as $operation) {

                if (!empty($operation['security'])) {

                    $this->assertArrayHasKey(
                        'bearerAuth',
                        $operation['security'][0]
                    );
                }
            }
        }
    }

    public function test_model_schemas_are_generated()
    {
        $spec = $this->openApiSpec();

        $schemas = $spec['components']['schemas'];

        $this->assertNotEmpty($schemas);

        foreach ($schemas as $schema) {

            $this->assertSame('object', $schema['type']);
            $this->assertArrayHasKey('properties', $schema);
        }
    }

    public function test_all_referenced_models_exist()
    {
        $spec = $this->openApiSpec();

        $schemas = $spec['components']['schemas'];
        $refs = $this->collectRefs($spec['paths']);

        foreach ($refs as $ref) {

            $this->assertArrayHasKey(
                $ref,
                $schemas,
                "Missing schema for ref: {$ref}"
            );
        }
    }

    public function test_no_broken_refs_exist()
    {
        $spec = $this->openApiSpec();

        $refs = $this->collectRefs($spec['paths']);

        foreach ($refs as $ref) {

            $this->assertNotEmpty($ref);

            $this->assertStringStartsWith(
                '#/components/schemas/',
                "#/components/schemas/{$ref}"
            );
        }
    }

    public function test_relationship_refs_are_valid()
    {
        $spec = $this->openApiSpec();

        foreach ($spec['components']['schemas'] as $name => $schema) {

            if (!isset($schema['properties'])) {
                continue;
            }

            foreach ($schema['properties'] as $prop => $value) {

                if (($value['type'] ?? null) === 'array') {

                    $this->assertArrayHasKey(
                        'items',
                        $value,
                        "Missing items in {$name}.{$prop}"
                    );

                    $this->assertArrayHasKey(
                        '$ref',
                        $value['items'],
                        "Missing $ref in {$name}.{$prop}"
                    );
                }

                if (isset($value['$ref'])) {

                    $this->assertStringStartsWith(
                        '#/components/schemas/',
                        $value['$ref']
                    );
                }
            }
        }
    }

    public function test_no_empty_schema_keys_exist()
    {
        $spec = $this->openApiSpec();

        foreach ($spec['components']['schemas'] as $key => $schema) {

            $this->assertNotEmpty($key);
            $this->assertIsArray($schema);
        }
    }

    public function test_servers_block_exists()
    {
        $spec = $this->openApiSpec();

        $this->assertArrayHasKey('servers', $spec);
        $this->assertSame(
            'Current server',
            $spec['servers'][0]['description']
        );
    }

    private function collectRefs(array $paths): array
    {
        $refs = [];

        array_walk_recursive($paths, function ($value, $key) use (&$refs) {

            if ($key === '$ref') {
                $refs[] = str_replace('#/components/schemas/', '', $value);
            }
        });

        return array_unique($refs);
    }
}