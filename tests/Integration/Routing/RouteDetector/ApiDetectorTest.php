<?php
namespace Suphle\Tests\Integration\Routing\RouteDetector;

use Suphle\Contracts\Config\Router;

use Suphle\Testing\TestTypes\IsolatedComponentTest;

use Suphle\Tests\Integration\Generic\CommonBinds;

use Suphle\Tests\Mocks\Modules\ModuleOne\Config\RouterMock;

class ApiDetectorTest extends IsolatedComponentTest {
    
    use CommonBinds, RouteDetectorAsserter {

        CommonBinds::concreteBinds as commonConcretes;
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