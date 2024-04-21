<?php
namespace Suphle\Tests\Integration\Routing\RouteDetector;

use Suphle\Contracts\Config\Router;

use Suphle\Hydration\Container;

use Suphle\Testing\TestTypes\ModuleLevelTest;

use Suphle\Tests\Mocks\Modules\ModuleOne\{Config\RouterMock, Meta\ModuleOneDescriptor};

class ApiDetectorTest extends ModuleLevelTest {
    
    use RouteDetectorAsserter;

    protected function getModules ():array {

        return [new ModuleOneDescriptor(new Container)];
    }

    public function test_will_include_api_routes () {

        $collectionDetails = $this->getDetector()->compileCollectionDetails()["v1"]; // when

        $this->assertFoundGivenPatterns( // then

            $collectionDetails,

            $this->apiPaths()
        );
    }

    public function apiPaths ():array {

        return [
            ["secure-segment/"],
            ["api-segment/"],
            ["segment/id/"],
            ["cascade/"]
        ];
    }
}