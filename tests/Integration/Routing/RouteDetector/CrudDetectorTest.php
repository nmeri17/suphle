<?php
namespace Suphle\Tests\Integration\Routing\RouteDetector;

use Suphle\Contracts\Config\Router;

use Suphle\Routing\CollectionRouteDetector;

use Suphle\Testing\{TestTypes\ModuleLevelTest, Proxies\WriteOnlyContainer};

use Suphle\Tests\Mocks\Modules\ModuleOne\{Config\RouterMock, Routes\Crud\BasicRoutes, Meta\ModuleOneDescriptor};

class CrudDetectorTest extends ModuleLevelTest {
    
    use RouteDetectorAsserter;

    protected function getModules (): array
    {
        return [
            $this->replicateModule(ModuleOneDescriptor::class, function(WriteOnlyContainer $container) {

                $container->replaceWithMock(Router::class, RouterMock::class, [

                    "mirrorsCollections" => false,

                    "browserEntryRoute" => BasicRoutes::class
                ]);
            })
        ];
    }

    public function test_can_detect_all_crud_routes () {

        $targetPattern = "SAVE__ALLh"; // given

        // when
        $collectionDetails = $this->getDetector()

        ->compileCollectionDetails(["NON__EXISTENTh"])[0]; // this route throws an error
        
        // then
        $this->assertArrayHasKey($targetPattern, $collectionDetails);

        $this->assertFoundGivenPatterns(

            $collectionDetails[$targetPattern]["child_collection"],

            $this->generalCrudPaths()
        );
    }

    public function generalCrudPaths (): array
    {

        return [
            [""],

            ["create/"],

            ["edit/id/"],

            ["save/"],

            ["id/"],

            ["edit/"],

            ["delete/"],

            ["search/"]
        ];
    }

    public function test_wont_detect_disabled_crud_routes () {

        $targetPattern = "DISABLE__SOMEh"; // given

        $collectionDetails = $this->getDetector()

        ->compileCollectionDetails(["NON__EXISTENTh"])[0]; // when

        // then
        $this->assertArrayHasKey($targetPattern, $collectionDetails);

        $this->assertNotFoundGivenPatterns(

            $collectionDetails[$targetPattern]["child_collection"],

            $this->crudPathsToBeAbsent()
        );
    }

    public function crudPathsToBeAbsent ():array {

        return [
            ["save/"]
        ];
    }
}