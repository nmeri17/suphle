<?php
namespace Suphle\Tests\Integration\Routing\RouteDetector\Prefix;

use Suphle\Contracts\Config\Router as RouterContract;

use Suphle\Config\Router;

use Suphle\Testing\{TestTypes\ModuleLevelTest, Proxies\WriteOnlyContainer};

use Suphle\Tests\Integration\Routing\RouteDetector\RouteDetectorAsserter;

use Suphle\Tests\Mocks\Modules\ModuleOne\{Config\RouterMock, Meta\ModuleOneDescriptor};

class PrefixDetectorTest extends ModuleLevelTest {
    
    use RouteDetectorAsserter;

    protected function getModules (): array
    {

        return [
            $this->replicateModule(ModuleOneDescriptor::class, function (WriteOnlyContainer $container) {

                $container->replaceWithMock(RouterContract::class, Router::class, [

                    // "mirrorsCollections" => false // i doubt the necessity of this in the first place
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