<?php
namespace Suphle\Tests\Integration\Routing\RouteDetector\Prefix;

use Suphle\Contracts\Config\Router;

use Suphle\Testing\{TestTypes\ModuleLevelTest, Proxies\WriteOnlyContainer};

use Suphle\Tests\Integration\Routing\RouteDetector\RouteDetectorAsserter;

use Suphle\Tests\Mocks\Modules\ModuleOne\{Config\RouterMock, Routes\Prefix\ActualEntry, Meta\ModuleOneDescriptor};

class PrefixDetectorTest extends ModuleLevelTest {
    
    use RouteDetectorAsserter;

    protected function getModules (): array
    {

        return [
            $this->replicateModule(ModuleOneDescriptor::class, function (WriteOnlyContainer $container) {

                $container->replaceWithMock(Router::class, RouterMock::class, [

                    "mirrorsCollections" => false,

                    "browserEntryRoute" => ActualEntry::class
                ]);
            })
        ];
    }

    public function test_can_dig_through_to_innermost_pattern () {

        $this->assertMatchesChildPatterns(

            $this->getDetector()->compileCollectionDetails()[0],

            "first/middle/third"
        );
    }
}