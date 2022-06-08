<?php
	namespace Tilwa\Tests\Integration\Routing;

	use Tilwa\Routing\{PathPlaceholders, RouteManager, PatternIndicator, CollectionMethodToUrl};

	use Tilwa\Request\RequestDetails;

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

			$container = $this->container;

			$sut = $container->getClass(PathPlaceholders::class);

			// given
			$router = $this->replaceConstructorArguments (RouteManager::class, [ // pulling some dependencies from container so their constructors can run

				"placeholderStorage" => $sut,

				"requestDetails" => $container->getClass(RequestDetails::class),

				"patternIndicator" => $this->negativeDouble(PatternIndicator::class),

				"urlReplacer" => $container->getClass(CollectionMethodToUrl::class)
			], [

				"entryRouteMap" => [BrowserNoPrefix::class]
			]);

			$router->findRenderer(); // when

			$this->assertSame(

				$expectedPlaceholders, $sut->getAllSegmentValues()
			); // then
		}

		public function pathsAndPlaceholders ():array {

			return [
				["/segment", []],

				["/segment-segment/5", ["id" => "5"]],

				["segment/5/segment/10", ["id" => "5", "id2" => "10"]]
			];
		}

		public function test_can_extract_all_method_segments () {

			$sut = $this->container->getClass(CollectionMethodToUrl::class);

			$segments = $sut->splitIntoSegments("SEGMENT/id/SEGMENT2/?(id2/?)?"); // when

			$this->assertSame(["SEGMENT", "id", "SEGMENT2", "id2"], $segments); // then
		}
	}
?>