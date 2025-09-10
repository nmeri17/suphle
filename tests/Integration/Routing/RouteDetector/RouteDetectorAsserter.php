<?php
namespace Suphle\Tests\Integration\Routing\RouteDetector;

use Suphle\Routing\RouteDetectorService;

trait RouteDetectorAsserter {

    /**
     * Asserts that the given routes contain the expected patterns
    */
    protected function assertFoundGivenPatterns(array $routes, array $expectedPatterns): void {
        $matchedAll = true;

        foreach ($expectedPatterns as $expectedPattern) {
            $matchedAll = !empty(array_filter($routes, function ($route) use ($expectedPattern) {
                return $expectedPattern === $route['path'];
            }));

            if (!$matchedAll) {
                var_dump($expectedPattern, $routes);
                break;
            }
        }

        $this->assertTrue($matchedAll);
    }

    /**
     * Asserts that the given routes do not contain the expected patterns
    */
    protected function assertNotFoundGivenPatterns(array $routes, array $expectedPatterns): void {
        $missedAll = true;

        foreach ($expectedPatterns as $expectedPattern) {
            $missedAll = empty(array_filter($routes, function ($route) use ($expectedPattern) {
                return $expectedPattern === $route['path'];
            }));

            if (!$missedAll) {
                var_dump($expectedPattern, $routes);
                break;
            }
        }

        $this->assertTrue($missedAll);
    }

    protected function getService(): RouteDetectorService {
        return $this->getContainer()->getClass(RouteDetectorService::class);
    }

    /**
     * Asserts that routes contain the expected HTTP methods
    */
    protected function assertRoutesHaveMethods(array $routes, array $expectedMethods): void {
        $routeMethods = array_column($routes, 'method');
        
        foreach ($expectedMethods as $expectedMethod) {
            $this->assertContains($expectedMethod, $routeMethods);
        }
    }

    /**
     * Asserts that routes have the expected renderers
    */
    protected function assertRoutesHaveRenderers(array $routes, array $expectedRenderers): void {
        $routeRenderers = array_filter(array_column($routes, 'renderer'));
        
        foreach ($expectedRenderers as $expectedRenderer) {
            $this->assertContains($expectedRenderer, $routeRenderers);
        }
    }

    /**
     * Asserts that routes have the expected placeholders
    */
    protected function assertRoutesHavePlaceholders(array $routes, array $expectedPlaceholders): void {
        $allPlaceholders = [];
        foreach ($routes as $route) {
            $allPlaceholders = array_merge($allPlaceholders, $route['placeholders']);
        }
        
        foreach ($expectedPlaceholders as $expectedPlaceholder) {
            $this->assertContains($expectedPlaceholder, $allPlaceholders);
        }
    }
}