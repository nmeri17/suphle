<?php
namespace Suphle\Tests\Integration\Routing\RouteDetector;

use Suphle\Contracts\Config\Router;

use Suphle\Testing\TestTypes\IsolatedComponentTest;

use Suphle\Tests\Integration\Generic\CommonBinds;

use Suphle\Tests\Mocks\Modules\ModuleOne\{Config\RouterMock, Routes\Prefix\ActualEntry};

class PrefixDetectorTest extends IsolatedComponentTest {
    
    use CommonBinds, RouteDetectorAsserter {

        CommonBinds::concreteBinds as commonConcretes;
    }

    protected function simpleBinds ():array {

        return parent::simpleBinds();
    }

    protected function concreteBinds(): array
    {

        return array_merge($this->commonConcretes(), [

            Router::class => $this->positiveDouble(RouterMock::class, [

                "mirrorsCollections" => false,

                "browserEntryRoute" => ActualEntry::class
            ])
        ]);
    }

    public function test_can_dig_through_to_innermost_pattern () {

        $this->assertMatchesChildPatterns(

            $this->getDetector()->compileCollectionDetails()[0],

            "first/middle/third"
        );
    }
}