<?php
	namespace Tilwa\Tests\Integration\Services\Search;

	use Tilwa\Contracts\Database\OrmDialect;

	use Tilwa\Request\PayloadStorage;

	use Tilwa\Testing\{TestTypes\IsolatedComponentTest, Condiments\DirectHttpTest};

	use Tilwa\Tests\Integration\Generic\CommonBinds;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Concretes\Services\Search\SimpleSearchService;

	use stdClass;

	/**
	 * @backupGlobals enabled . Without this, the $_GET sticks around in-between tests
	*/
	class SimpleSearchTest extends IsolatedComponentTest {

		use DirectHttpTest, CommonBinds;

		private $ormDialect, $searchService, $model,

		$baseUrl = "/search/?q=jobs&";

		public function setUp ():void {

			parent::setUp();

			$this->ormDialect = $this->negativeDouble(OrmDialect::class);

			
			$this->model = new stdClass;
		}

		public function test_calls_class_methods_matching_queries () {

			$this->setHttpParams($this->baseUrl . "custom_filter=5"); // given
			
			// then
			$this->mockCalls([

				"addWhereClause" => [$this->never(), [$this->anything()]]
			], $this->ormDialect);

			$searchService = $this->getSearchService([

				"custom_filter" => [1, [

					$this->equalTo($this->model), 5
				]]
			]);

			$searchService->convertToQuery($this->model, "q"); // when
		}

		public function test_skips_class_methods_not_matching_queries () {

			$this->setHttpParams($this->baseUrl . "database_column=foo"); // given

			$searchService = $this->getSearchService([

				"custom_filter" => [$this->never(), [

					$this->equalTo($this->model), 5
				]]
			]); // then

			$searchService->convertToQuery($this->model, "q"); // when
		}

		public function test_calls_ormDialect_when_sees_custom_method () {
			
			$this->mockCalls([

				"addWhereClause" => [$this->atLeastOnce(), [$this->anything()]]
			], $this->ormDialect); // then

			$this->setHttpParams($this->baseUrl . "database_column=foo"); // given

			$this->getSearchService()->convertToQuery($this->model, "q"); // when
		}

		private function getSearchService (array $mockMethods = []):SimpleSearchService {

			return $this->replaceConstructorArguments(SimpleSearchService::class, [

				"ormDialect" => $this->ormDialect,

				"payloadStorage" => $this->container->getClass(PayloadStorage::class) // if we rely on a stub for this, PHPUnit gives a static $_GET that is never affected by our url updates
			], [], $mockMethods);
		}
	}
?>