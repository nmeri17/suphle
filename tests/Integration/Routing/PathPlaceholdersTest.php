<?php
	namespace Suphle\Tests\Integration\Routing;

	use Suphle\Routing\{PathPlaceholders, RouteManager, PatternIndicator, CollectionMethodToUrl};

	use Suphle\Request\RequestDetails;

	use Suphle\Tests\Mocks\Modules\ModuleOne\Routes\BrowserNoPrefix;

	class PathPlaceholdersTest extends TestsRouter {

		/**
	     * @dataProvider pathsAndPlaceholders
	     */
		public function test_replaceInPattern (string $activePath, array $expectedPlaceholders) {

			$this->setHttpParams($activePath);

			$sut = $this->getContainer()->getClass(PathPlaceholders::class);

			$this->buildRouter($sut) // given

			->findRenderer(); // when

			$sut->exchangeTokenValues($activePath);

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

		private function buildRouter (PathPlaceholders $pathPlaceholders):RouteManager {

			$container = $this->getContainer();

			return $this->replaceConstructorArguments (RouteManager::class, [ // pulling some dependencies from container so their constructors can run

				"placeholderStorage" => $pathPlaceholders,

				"requestDetails" => $container->getClass(RequestDetails::class),

				"patternIndicator" => $this->negativeDouble(PatternIndicator::class),

				"urlReplacer" => $container->getClass(CollectionMethodToUrl::class)
			], [

				"entryRouteMap" => [BrowserNoPrefix::class]
			]);
		}

		public function test_can_extract_all_method_segments () {

			$sut = $this->getContainer()->getClass(CollectionMethodToUrl::class);

			$segments = $sut->splitIntoSegments("SEGMENT/id/SEGMENT2/?(id2/?)?"); // when

			$this->assertSame(["SEGMENT", "id", "SEGMENT2", "id2"], $segments); // then
		}
	}
?>