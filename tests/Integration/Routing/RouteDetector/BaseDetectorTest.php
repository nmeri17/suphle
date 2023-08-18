<?php
namespace Suphle\Tests\Integration\Routing;

use Suphle\Contracts\Config\Router;

use Suphle\Routing\CollectionRouteDetector;

use Suphle\Testing\TestTypes\IsolatedComponentTest;

use Suphle\Tests\Integration\Generic\CommonBinds;

use Suphle\Tests\Mocks\Modules\ModuleOne\Config\RouterMock;

class BaseDetectorTest extends IsolatedComponentTest {
    
    use CommonBinds {

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
// low level (prefixes), api, crud, auth
    public function test_can_detect_all_high_level_routes () {

        $sut = $this->container->getClass(CollectionRouteDetector::class);

        $matchedAll = true;

        $collectionDetails = $sut->findRenderers();
var_dump($collectionDetails);
    	foreach ($this->expectedPatternDetails() as $detailEntry) {

            $matchedAll = !empty(array_filter($collectionDetails,

                fn ($details) => strtolower($detailEntry[0]) == strtolower($details["url"])
            ));

            if (!$matchedAll) {

                var_dump($detailEntry);

                break;
            }
        }

        $this->assertTrue($matchedAll);
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