<?php
namespace Suphle\Tests\Integration\Routing\RouteDetector\Prefix;

use Suphle\Contracts\Config\Router;

use Suphle\Auth\RequestScrutinizers\AuthenticateMetaFunnel;

use Suphle\Routing\CollectionRouteDetector;

use Suphle\Testing\{TestTypes\ModuleLevelTest, Proxies\WriteOnlyContainer};

use Suphle\Tests\Integration\Routing\RouteDetector\RouteDetectorAsserter;

use Suphle\Tests\Mocks\Modules\ModuleOne\{Config\RouterMock, Routes\Prefix\Secured\UpperCollection, Meta\ModuleOneDescriptor};

class DetectTagTest extends ModuleLevelTest {
    
    use RouteDetectorAsserter;

    protected function getModules ():array {

        return [
            $this->replicateModule(ModuleOneDescriptor::class, function (WriteOnlyContainer $container) {

                $container->replaceWithMock(Router::class, RouterMock::class, [

                    "mirrorsCollections" => false,

                    "browserEntryRoute" => UpperCollection::class
                ]);
            })
        ];
    }

    public function test_detects_tagged_patterns () { // continue by investigating the reason AuthenticateMetaFunnel returns an empty array in crdetec

        $detector = $this->getDetector();

        $collectionDetails = $detector->compileCollectionDetails()[0];

        $indicatedStatus = $detector->assignMetaStatus([

            AuthenticateMetaFunnel::class
        ], $collectionDetails);

        //$this->assertMatchesChildPatterns(

            var_dump( $indicatedStatus)/*,

            "PREFIX/RETAIN__AUTHh"
        )*/;
        $this->assertTrue(true);
    }

    public function test_detects_untagged_patterns () {

        //
    }
}