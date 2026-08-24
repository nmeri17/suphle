<?php

namespace Suphle\Tests\Integration\Routing;

use Suphle\Routing\{ModuleRequestRouter, Structures\RouteInfo};

use Suphle\Contracts\Config\Router;

use Suphle\Testing\{TestTypes\ModuleLevelTest, Proxies\WriteOnlyContainer};

use Suphle\Tests\Mocks\Modules\ModuleOne\Meta\ModuleOneDescriptor;

use Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators\{BaseCoordinator, ApiEntryCoordinator};

class TestsRouter extends ModuleLevelTest {

    protected array $coordinators = [
        BaseCoordinator::class, ApiEntryCoordinator::class
    ];

    protected function getModules(): array
    {

        return [
            $this->replicateModule(ModuleOneDescriptor::class, function (WriteOnlyContainer $container) {

                $container->replaceWithMock(RouterContract::class, Router::class, [
                    
                    "getCoordinatorClassesToScan" => $this->coordinators
                ]);
            })
        ];
    }

    protected function findRouteInfo(string $url, string $httpMethod = "get", array $payload = null): ?RouteInfo
    {

        if (is_null($payload)) {
            $this->$httpMethod($url);
        } else {
            $this->$httpMethod($url, $payload);
        }

        return $this->container->getClass(ModuleRequestRouter::class)->getFoundRoute();
    }

    public function pathsAndPlaceholders(): array
    {

        return [
            ["/segment", []],

            ["/segment-segment/5", ["id" => "5"]],

            ["segment/5/segment/10", ["id" => "5", "id2" => "10"]]
        ];
    }

    public function pathsToHandler(): array
    {

        return [
            [ "indexHandler", "/"],

            [ "plainSegment", "/segment"],
            [ "plainSegment", "/segment/"],

            [ "simplePair", "/segment/5"],
            [ "simplePair", "/segment/5/"],

            [ "hyphenatedSegments", "/segment-segment/5"],
            [ "hyphenatedSegments", "/segment-segment/5/"],

            [ "underscoredSegments", "/segment_segment/5"]
        ];
    }
}
