<?php
namespace Suphle\Tests\Integration\Routing\RouteDetector;

use Suphle\Contracts\Config\Router;

use Suphle\Testing\TestTypes\IsolatedComponentTest;

use Suphle\Tests\Integration\Generic\CommonBinds;

use Suphle\Tests\Mocks\Modules\ModuleOne\Config\RouterMock;

class BaseDetectorTest extends IsolatedComponentTest {
    
    use CommonBinds, RouteDetectorAsserter {

        CommonBinds::concreteBinds as commonConcretes;
    }

    protected function simpleBinds ():array {

        return parent::simpleBinds(); // the trait version automatically binds the average config
    }

    protected function concreteBinds(): array
    {

        return array_merge($this->commonConcretes(), [

            Router::class => $this->positiveDouble(RouterMock::class, [

                "mirrorsCollections" => false
            ])
        ]);
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