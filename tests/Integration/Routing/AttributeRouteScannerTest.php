<?php
namespace Suphle\Tests\Integration\Routing;

use Suphle\Routing\{AttributeRouteScanner, Analysis\RouteAnalysisService};

use Suphle\Contracts\Config\Router;

use Suphle\Testing\{TestTypes\ModuleLevelTest, Proxies\WriteOnlyContainer};

use Suphle\Tests\Mocks\Modules\ModuleOne\{ Meta\ModuleOneDescriptor, Config\RouterMock};

class AttributeRouteScannerTest extends ModuleLevelTest {

    protected function getModules(): array {

        return [
            $this->replicateModule(ModuleOneDescriptor::class, function (WriteOnlyContainer $container) {

                $container->replaceWithMock(Router::class, RouterMock::class, []);
            })
        ];
    }

    /**
     * Verifies that the scanner correctly traverses the filesystem and 
     * triggers the analyzer with the correct FQCN and Module Name.
     */
    public function test_scanAllModules_finds_physical_files_and_delegates() {

        $container = $this->getContainer();

        $analyzerMock = $this->positiveDouble(RouteAnalysisService::class, [

            "analyzeCoordinator" => [["path" => "/found"]]
        ], [
            "analyzeCoordinator" => [1, [$this->stringContains("TestCoordinator"), "ModuleOne"]]
        ]);

        $this->massProvide([

            RouteAnalysisService::class => $analyzerMock,
        ]);

        $scanner = $container->getClass(AttributeRouteScanner::class);

        // When
        $allRoutes = $scanner->scanAllModules();

        // Then
        $this->assertNotEmpty($allRoutes);

        $this->assertEquals("/found", $allRoutes[0]["path"]);
    }

    /**
     * Verifies that the RecursiveIterator successfully enters subdirectories 
     * within the Coordinator path.
     */
    public function test_scanAllModules_finds_nested_coordinators() {

        $container = $this->getContainer();

        $scanner = $container->getClass(AttributeRouteScanner::class);

        // Act
        $routes = $scanner->scanAllModules();

        // Assert: Using standard foreach since we're not on PHP 8.4 yet
        $hasNested = false;

        foreach ($routes as $route) {

            $coordinator = $route['coordinator'] ?? '';

            if (str_contains($coordinator, 'SubCoordinator') || str_contains($coordinator, 'Nested')) {

                $hasNested = true;

                break;
            }
        }

        $this->assertTrue($hasNested, "Scanner failed to traverse into nested Coordinator directories.");
    }
}