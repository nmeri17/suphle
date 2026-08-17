<?php
namespace Suphle\Tests\Integration\Routing\RouteDetector;

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
}