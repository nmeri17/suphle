<?php
	namespace Suphle\Tests\Unit\Routing;

	use Suphle\Contracts\Routing\RouteCollection;

	use Suphle\Routing\{RouteManager, Structures\PlaceholderCheck, CollectionMethodToUrl, PathPlaceholders};

	use Suphle\Testing\{TestTypes\IsolatedComponentTest, Condiments\DirectHttpTest};

	use Suphle\Tests\Integration\Generic\CommonBinds;

	use Suphle\Tests\Mocks\Modules\ModuleOne\Routes\{BrowserNoPrefix, Crud\AuthenticateCrudCollection};

	/**
	 * Note: all urls passed to methods apart from [recursiveSearch] should have no surrounding slashes in order to match parsed patterns
	*/
	class RouteManagerTest extends IsolatedComponentTest {

		use DirectHttpTest, CommonBinds;

		private $sut, $collection, $sutName = RouteManager::class;

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

			$this->assertNotNull($result);

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

			$sut = $this->container->getClass(CollectionMethodToUrl::class);

			$result = $sut->replacePlaceholders($methodName, RouteManager::PLACEHOLDER_REPLACEMENT);

			$this->assertSame($regexVersion, $result->regexifiedUrl());
		}

		public function regexFormData ():array {

			$placeholder = RouteManager::PLACEHOLDER_REPLACEMENT;

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

		public function test_patternPlaceholderDetails_returns_correctly_transformed () {

			$result = $this->sut->patternPlaceholderDetails(

				$this->collection->_getPatterns() // given 
			); // when

			$placeholder = RouteManager::PLACEHOLDER_REPLACEMENT;

			$this->assertEqualsCanonicalizing([

				"SEGMENT/?",

				"SEGMENT/$placeholder/?",

				"SEGMENT-SEGMENT/$placeholder/?",

				"SEGMENT_SEGMENT/$placeholder/?",

				"SEGMENT/$placeholder/SEGMENT/?($placeholder/?)?",

				""
			], array_column($result, "url")); // then
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

				"methodPartiallyMatchPattern" => [(is_countable($patterns) ? count($patterns) : 0)-1, []] // omit SEGMENT, the only literal pattern in collection
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

		/**
		 * @dataProvider placeholderMethods
		*/
		public function test_injects_correct_placeholders_for_storage (string $urlPattern, string $methodPattern, array $expectedPlaceholders) {

			$placeholderStorage = $this->positiveDouble(PathPlaceholders::class, [], [

				"foundSegments" => [1, [$expectedPlaceholders]] // then
			]);

			$router = $this->replaceConstructorArguments ($this->sutName, [

				"placeholderStorage" => $placeholderStorage,

				"urlReplacer" => $this->container->getClass(CollectionMethodToUrl::class)
			]);

			$router->methodPartiallyMatchPattern(

				$urlPattern, [$methodPattern] // given
			); // when
		}

		public function placeholderMethods ():array {

			return [
				["segment-segment/5", "SEGMENT__SEGMENTh_id", ["id"]],

				["segment/5/segment/5", "SEGMENT_id_SEGMENT_id2O", ["id", "id2"]]
			];
		}

		public function test_crud_can_find_active_handler () {

			$sut = $this->container->getClass($this->sutName);

			$collection = $this->container->getClass(AuthenticateCrudCollection::class);

			$collection->SECURE__SOMEh();

			$possibleRenderers = $collection->_getLastRegistered();

			$methodName = $sut->findActiveCrud(array_keys($possibleRenderers), "edit/5"); // when

			$this->assertSame("EDIT_id", $methodName); // then
		}
	}
?>