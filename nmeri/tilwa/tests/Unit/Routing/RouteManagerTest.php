<?php
	namespace Tilwa\Tests\Unit\Routing;

	use Tilwa\Contracts\Routing\RouteCollection;

	use Tilwa\Routing\{RouteManager, BaseCollection, Structures\PlaceholderCheck};

	use Tilwa\Testing\{TestTypes\IsolatedComponentTest, Condiments\DirectHttpTest};

	use Tilwa\Tests\Integration\Generic\CommonBinds;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Routes\BrowserNoPrefix;

	/**
	 * Note: all urls passed to methods apart from [recursiveSearch] should have no surrounding slashes in order to match parsed patterns
	*/
	class RouteManagerTest extends IsolatedComponentTest {

		use DirectHttpTest, CommonBinds;

		private $sut, $collection, $sutName = RouteManager::class,

		$placeholderCharacter = "[\w-]+";

		protected function setUp ():void {

			parent::setUp();

			$this->sut = $this->container->getClass($this->sutName);

			$this->collection = $this->container->getClass(BrowserNoPrefix::class);
		}

		/**
	     * @dataProvider compareOptionalUrl
	     */
		public function test_route_compare_optional (string $path) {

			$result = $this->sut->findMatchingMethod($path, $this->collection->_getPatterns());

			$this->assertSame("SEGMENT_id_SEGMENT_id2O", $result->getMethodName());
		}

		public function compareOptionalUrl ():array {

			return [
				[ "segment/5/segment/5"],

				[ "segment/5/segment"]
			];
		}

		/**
	     * @dataProvider regexFormData
	     */
		public function test_regex_form (string $methodName, string $regexVersion) {

			$this->assertSame($regexVersion, $this->sut->regexForm($methodName));
		}

		public function regexFormData ():array {

			$placeholder = "[\w-]+";

			return [
				[
					"SEGMENT_id_SEGMENT_id2O",
					"SEGMENT/$placeholder/SEGMENT/?($placeholder/?)?"
				],
				[
					"SEGMENT_id", "SEGMENT/$placeholder/?"
				]
			];
		}

		// some tests above have to go or be modified
		public function test_getPlaceholderMethods_returns_correctly_transformed () {

			$result = $this->sut->getPlaceholderMethods(

				$this->collection->_getPatterns() // given 
			); // when

			$placeholder = $this->placeholderCharacter;

			$this->assertEqualsCanonicalizing([

				"SEGMENT/$placeholder/?",

				"SEGMENT-SEGMENT/$placeholder/?",

				"SEGMENT_SEGMENT/$placeholder/?",

				"SEGMENT/$placeholder/SEGMENT/?($placeholder/?)?",

				""
			], array_values($result)); // then
		}

		public function test_method_can_partly_match_path () {

			$result = $this->sut->methodPartiallyMatchPattern(
				"segment-segment/5/segment", // given

				$this->collection->_getPatterns()
			); // when

			$expected = new PlaceholderCheck( "segment-segment/5/", "SEGMENT__SEGMENTh_id");

			$this->assertEquals($expected, $result); // then
		}

		public function test_findMatchingMethod_stops_at_literal_match () {

			$sut = $this->positiveDouble($this->sutName, [], [

				"methodPartiallyMatchPattern" => [0, []] // SEGMENT is the only literal pattern in collection
			]); // then

			$result = $sut->findMatchingMethod( // when 

				"segment", $this->collection->_getPatterns() // given
			);

			$this->assertSame("SEGMENT", $result->getMethodName());
		}

		public function test_findMatchingMethod_skips_when_not_literal () {

			$patterns = $this->collection->_getPatterns();

			$sut = $this->positiveDouble($this->sutName, [], [

				"methodPartiallyMatchPattern" => [count($patterns)-1, []] // omit SEGMENT, the only literal pattern in collection
			]); // then

			foreach ($patterns as $method)

				$sut->findMatchingMethod( // when 

					$method, $patterns // given
				);
		}

		/**
		 * @dataProvider pathToCollectionMethod
		*/
		public function test_findMatchingMethod_finds_correct_method (string $url, string $expectedMethod) {

			$patterns = $this->collection->_getPatterns();

			$result = $this->sut->findMatchingMethod( // when 

				$url, $patterns // given
			);

			$this->assertNotNull($result);

			$this->assertSame($expectedMethod, $result->getMethodName()); // then
		}

		public function pathToCollectionMethod ():array {

			return [
				[ "", "_index"],

				[ "segment", "SEGMENT"],

				[ "segment/5", "SEGMENT_id"],

				[ "segment-segment/5", "SEGMENT__SEGMENTh_id"],

				[ "segment_segment/5", "SEGMENT__SEGMENTu_id"],

				[ "segment/5/segment/5", "SEGMENT_id_SEGMENT_id2O"],
				[ "segment/5/segment", "SEGMENT_id_SEGMENT_id2O"]
			];
		}
	}
?>