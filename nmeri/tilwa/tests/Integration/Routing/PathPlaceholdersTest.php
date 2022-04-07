<?php
	namespace Tilwa\Tests\Integration\Routing;

	use Tilwa\Routing\{PathPlaceholders, RouteManager};

	use Tilwa\Testing\{TestTypes\IsolatedComponentTest, Condiments\DirectHttpTest};

	use Tilwa\Tests\Integration\Generic\CommonBinds;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Routes\BrowserNoPrefix;

	class PathPlaceholdersTest extends IsolatedComponentTest {

		use DirectHttpTest, CommonBinds;

		/**
	     * @dataProvider pathsAndPlaceholders
	     */
		public function test_replaceInPattern (string $activePath, array $expectedPlaceholders) {

			$this->setHttpParams($activePath);

			$sut = $this->container->getClass(PathPlaceholders::class);

			// given
			$router = $this->replaceConstructorArguments (RouteManager::class, [

				"placeholderStorage" => $sut
			], [

				"finishCollectionHousekeeping" => null,

				"entryRouteMap" => [BrowserNoPrefix::class]
			]);

			$router->findRenderer(); // when

			$this->assertEqualsCanonicalizing(

				$expectedPlaceholders, $sut->getAllSegmentValues()
			); // then
		}

		public function pathsAndPlaceholders ():array {

			return [
				["/segment", []],

				["/segment-segment/5", ["id" => 5]],

				["segment/5/segment/10", ["id" => 5, "id2" => 10]]
			];
		}
	}
?>