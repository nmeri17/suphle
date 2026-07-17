<?php
namespace Suphle\Tests\Integration\Routing\RouteDetector;

use Suphle\Contracts\Config\Router as RouterContract;

use Suphle\Testing\{TestTypes\ModuleLevelTest, Proxies\WriteOnlyContainer};

use Suphle\Tests\Mocks\Modules\ModuleOne\{Meta\ModuleOneDescriptor};

class BaseDetectorTest extends ModuleLevelTest {
    
    use RouteDetectorAsserter;

    protected function getModules (): array
    {

        return [
            $this->replicateModule(ModuleOneDescriptor::class, function (WriteOnlyContainer $container) {

                /*$container->replaceWithMock(RouterContract::class, Router::class, [

                    "mirrorsCollections" => false
                ]);*/
            })
        ];
    }

    public function test_can_detect_all_high_level_routes () {

        $this->assertFoundGivenPatterns(

            $this->getDetector()->compileCollectionDetails()[0], // [0] is used to access browser based (un-versioned) routes

            $this->expectedPatternDetails()
        );
    }

    public function expectedPatternDetails (): array
    {

        return [
            [""],

            ["segment/"],

            ["segment/id/"],

            ["segment-segment/id/"],

            ["segment_segment/id/"],

            ["segment/id/segment/id2/"]
        ];
    }
}