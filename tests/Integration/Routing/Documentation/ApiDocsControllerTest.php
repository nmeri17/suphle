<?php
namespace Suphle\Tests\Integration\Routing\Documentation;

use Suphle\Contracts\Config\ComponentTemplates;

use Suphle\Routing\Documentation\ApiDocsComponentEntry;

use Suphle\Request\PayloadStorage;

use Suphle\Testing\{TestTypes\InstallComponentTest, Proxies\WriteOnlyContainer, Utilities\ArrayAssertions};

use Suphle\Tests\Mocks\Modules\ModuleOne\{Meta\ModuleOneDescriptor};

class ApiDocsControllerTest extends InstallComponentTest
{
    use ArrayAssertions;

    protected function componentEntry ():string {

        return ApiDocsComponentEntry::class;
    }

    protected function getModules ():array {

        return [
            $this->replicateModule(ModuleOneDescriptor::class, function (WriteOnlyContainer $container) {

                $config = ComponentTemplates::class;

                $container->replaceWithMock($config, $config, [

                    "getTemplateEntries" => [$this->componentEntry()]
                ]);
            })
        ];
    }

    /**
     * @dataProvider overrideOptions
    */
    public function test_can_eject_api_component (array $customOptions, ?array $depositArguments) {

        $cliFlags = $this->getCommandOptions($customOptions);

        $this->assertInstalledComponent($cliFlags);
    }

    public function test_can_access_api_docs_page()
    {
        $this->runInstallComponent($this->getCommandOptions());

        $response = $this->get("/api-docs");

        $response->assertOk();
        $response->assertSee("API Documentation");
        $response->assertSee("Route Details");
    }

    public function test_can_access_api_docs_json()
    {
        $response = $this->get("/api-docs/json");

        $response->assertOk();
        $response->assertHeader(PayloadStorage::CONTENT_TYPE_KEY, PayloadStorage::JSON_HEADER_VALUE);

        $structure = ["openapi", "info", "paths", "components"];
        
        $response->assertJsonStructure($structure); // $this->assertArrayHasKeys($structure, $response->json()); // or getContent+decode

        $this->assertJsonFragment([
            "openapi" => "3.0.0",
            "info" => [
                "title" => "Suphle API Documentation",
                "version" => "1.0.0"
            ]
        ]);
    }

    public function test_openapi_spec_contains_server_info()
    {
        $response = $this->get("/api-docs/json");
        
        $response->assertJsonFragment([
            "servers" => [
                ["url" => "/"]
            ]
        ]);
        $this->assertArrayHasPath("servers.0.description", $response->json());
    }

    public function test_openapi_spec_contains_paths()
    {
        $response = $this->get("/api-docs/json");
        
        foreach (["/api-docs", "/api-docs/json"] as $key)

            $this->assertArrayHasPath("paths.$key", $response->json());
    }

    public function test_path_items_have_correct_structure()
    {
        $response = $this->get("/api-docs/json");
        
        $apiDocsPath = $response->json()["paths"]["/api-docs"];
        
        foreach (["summary", "description", "tags", "parameters", "responses"] as $key)

            $this->assertArrayHasPath("get.$key", $apiDocsPath);
    }

    public function test_path_items_have_responses()
    {
        $response = $this->get("/api-docs/json");
        
        $getOperation = $response->json()["paths"]["/api-docs"]["get"];

        $this->assertAssocArraySubset(["responses" => 200], $getOperation);
        
        foreach (["content", "description", PayloadStorage::JSON_HEADER_VALUE] as $key)

            $this->assertArrayHasPath("200.$key", $getOperation["responses"]);
    }

    public function test_components_section_exists()
    {
        $response = $this->get("/api-docs/json");
        
        $response->assertJsonStructure([
            "components" => ["schemas", "parameters"]
        ]);
    }

    public function test_route_tags_are_extracted_from_coordinator()
    {
        $response = $this->get("/api-docs/json");
        $jsonData = $response->json();
        
        // Find a route that should have a module tag
        foreach ($jsonData["paths"] as $path => $methods) {
            foreach ($methods as $method => $operation) {
                if (isset($operation["tags"]) && !empty($operation["tags"])) {
                    $this->assertIsArray($operation["tags"]);
                    
                    $this->assertIsString($operation["tags"][0]);
                    break 2;
                }
            }
        }
    }

    public function test_html_page_contains_route_data()
    {
        $response = $this->get("/api-docs");
        
        $response->assertOk();
        $content = $response->getContent();
        
        // Should contain route information
        $this->assertStringContainsString("Method", $content);
        $this->assertStringContainsString("Path", $content);
        $this->assertStringContainsString("Handler", $content);
    }
} 