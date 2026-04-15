<?php
namespace Suphle\Tests\Unit\Routing;

use Suphle\Routing\AttributeRouteScanner;
use Suphle\Routing\Analysis\RouteAnalysisService;
use Suphle\Modules\Structures\ActiveDescriptors;
use Suphle\Contracts\Modules\DescriptorInterface;
use Suphle\Contracts\Config\Router;
use Suphle\Testing\TestTypes\IsolatedComponentTest;
use Suphle\Tests\Integration\Generic\CommonBinds;
use Suphle\Tests\Mocks\Modules\ModuleOne\Config\RouterMock;

class AttributeRouteScannerTest extends IsolatedComponentTest {

    use CommonBinds;

    /**
     * Verifies that the scanner correctly traverses the filesystem and 
     * triggers the analyzer with the correct FQCN and Module Name.
     */
    public function test_scanAllModules_finds_physical_files_and_delegates() {

        $container = $this->getContainer();

        // 1. Create a MOCK for the Analyzer (3rd argument of positiveDouble)
        // Configuration: [invocations, [expected_arguments]]
        $analyzerMock = $this->positiveDouble(RouteAnalysisService::class, [

            "analyzeCoordinator" => [["path" => "/found"]] // Stub return value
        ], [
            "analyzeCoordinator" => [1, [$this->stringContains("TestCoordinator"), "ModuleOne"]] // Mock verification
        ]);

        // 2. Stub ActiveDescriptors to return the descriptor defined in CommonBinds
        $descriptor = $container->getClass(DescriptorInterface::class);

        $activeDescriptors = $this->positiveDouble(ActiveDescriptors::class, [

            "getOriginalDescriptors" => [$descriptor]
        ]);

        // 3. Inject our doubles into the container environment
        $this->massProvide([

            RouteAnalysisService::class => $analyzerMock,

            ActiveDescriptors::class => $activeDescriptors, // is this necessary
        ]);

        // 4. Hydrate SUT from container
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