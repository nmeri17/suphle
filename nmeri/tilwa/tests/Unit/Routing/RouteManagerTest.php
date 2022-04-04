<?php
	namespace Tilwa\Tests\Unit\Routing;

	use Tilwa\Contracts\Routing\RouteCollection;

	use Tilwa\Routing\{RouteManager, BaseCollection, Structures\PlaceholderCheck};

	use Tilwa\Testing\{TestTypes\IsolatedComponentTest, Condiments\DirectHttpTest};

	use Tilwa\Tests\Integration\Generic\CommonBinds;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Routes\BrowserNoPrefix;

	class RouteManagerTest extends IsolatedComponentTest {

		use DirectHttpTest, CommonBinds;

		private $sut, $collection, $sutName = RouteManager::class,

		$placeholderCharacter = "[\w-]";

		protected function setUp ():void {

			parent::setUp();

			$this->sut = $this->container->getClass($this->sutName);

			$this->collection = $this->container->getClass(BrowserNoPrefix::class);
		}

		public function test_route_compare_hyphen () {

			$this->setHttpParams("/segment-segment/5");

			$this->assertFalse($this->sut->routeCompare("/SEGMENT/id/", "get"));

			$this->assertTrue($this->sut->routeCompare("/SEGMENT-SEGMENT/id/", "get"));
		}

		/**
	     * @dataProvider compareOptionalData
	     */
		public function test_route_compare_optional (string $path) {

			$this->setHttpParams($path);

			$placeholder = $this->placeholderCharacter;

			$this->assertTrue($this->sut->routeCompare("SEGMENT/id/SEGMENT/($placeholder/)?", "get"));
		}

		public function compareOptionalData ():array {

			return [
				[ "/segment/5/segment/5/"], // without slash should be made to work too
				[ "/segment/5/segment"]
			];
		}

		/**
	     * @dataProvider regexFormData
	     */
		public function test_regex_form (string $rawForm, string $regexVersion) {

			$this->assertSame($this->sut->regexForm($rawForm), $regexVersion);
		}

		public function regexFormData ():array {

			$placeholder = "[\w-]";

			return [
				[
					"SEGMENT_id_SEGMENT_idO",
					"SEGMENT/$placeholder/SEGMENT/($placeholder/)?"
				],
				[
					"SEGMENT_id", "SEGMENT/$placeholder/"
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

				"SEGMENT/$placeholder/",

				"SEGMENT-SEGMENT/$placeholder/",

				"SEGMENT_SEGMENT/$placeholder/",

				"SEGMENT/$placeholder/SEGMENT/($placeholder/)?",

				""
			], array_values($result)); // then
		}

		public function test_method_can_partly_match_path () {
var_dump($this->collection->_getPatterns());
			$result = $this->sut->classMethodToPattern(
				"segment-segment/5/segment", // given

				$this->collection->_getPatterns()
			); // when

			$expected = new PlaceholderCheck( "segment-segment/5/", "SEGMENT__SEGMENTh_id");

			$this->assertEquals($expected, $result); // then
		}

		public function test_findMatchingMethod_stops_at_literal_match () {

			//
		}

		public function test_findMatchingMethod_skips_when_not_literal () {

			//
		}
	}
?>