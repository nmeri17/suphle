<?php
namespace Suphle\Tests\Integration\Routing\RouteDetector\Prefix;

use Suphle\Contracts\Config\Router;

use Suphle\Tests\Integration\Routing\RouteDetector\RouteDetectorAsserter;

use Suphle\Testing\{TestTypes\ModuleLevelTest, Proxies\WriteOnlyContainer};

use Suphle\Tests\Mocks\Modules\ModuleOne\{Config\RouterMock, Routes\Prefix\OuterCollection, Meta\ModuleOneDescriptor};

class ComputedPrefixTest extends ModuleLevelTest {
    
    use RouteDetectorAsserter;

    protected function getModules ():array {

        return [
            $this->replicateModule(ModuleOneDescriptor::class, function (WriteOnlyContainer $container) {

                $container->replaceWithMock(Router::class, RouterMock::class, [

                    "mirrorsCollections" => false,

                    "browserEntryRoute" => OuterCollection::class
                ]);
            })
        ];
    }

    public function test_correctly_works_with_collection_defined_prefix () {

        $this->assertMatchesChildPatterns(

            $this->getDetector()->compileCollectionDetails()[0],

            "outer/ignore-internal/with"
        );
    }
}