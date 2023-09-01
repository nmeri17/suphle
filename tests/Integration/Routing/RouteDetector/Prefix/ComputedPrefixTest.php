<?php
namespace Suphle\Tests\Integration\Routing\RouteDetector;

use Suphle\Contracts\Config\Router;

use Suphle\Testing\TestTypes\IsolatedComponentTest;

use Suphle\Tests\Integration\Generic\CommonBinds;

use Suphle\Tests\Mocks\Modules\ModuleOne\{Config\RouterMock, Routes\Prefix\OuterCollection};

class ComputedPrefixTest extends IsolatedComponentTest {
    
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

                "browserEntryRoute" => OuterCollection::class
            ])
        ]);
    }

    public function test_correctly_works_with_collection_defined_prefix () {

        $this->assertMatchesChildPatterns(

            $this->getDetector()->compileCollectionDetails()[0],

            "outer/ignore-internal/with"
        );
    }
}