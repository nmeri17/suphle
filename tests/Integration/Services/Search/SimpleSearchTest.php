<?php
	namespace Suphle\Tests\Integration\Services\Search;

	use Suphle\Contracts\Database\OrmDialect;

	use Suphle\Request\PayloadStorage;

	use Suphle\Testing\{TestTypes\IsolatedComponentTest, Condiments\DirectHttpTest};

	use Suphle\Tests\Integration\Generic\CommonBinds;

	use Suphle\Tests\Mocks\Modules\ModuleOne\Concretes\Services\Search\SimpleSearchService;

	use stdClass;

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

			$searchService->convertToQuery($this->model, ["q"]); // when
		}

		public function test_skips_class_methods_not_matching_queries () {

			$this->setHttpParams($this->baseUrl . "database_column=foo"); // given

			$searchService = $this->getSearchService([

				"custom_filter" => [$this->never(), [

					$this->equalTo($this->model), 5
				]]
			]); // then

			$searchService->convertToQuery($this->model, ["q"]); // when
		}

		public function test_calls_ormDialect_when_sees_custom_method () {
			
			$this->mockCalls([

				"addWhereClause" => [$this->atLeastOnce(), [$this->anything()]]
			], $this->ormDialect); // then

			$this->setHttpParams($this->baseUrl . "database_column=foo"); // given

			$this->getSearchService()->convertToQuery($this->model, ["q"]); // when
		}

		private function getSearchService (array $mockMethods = []):SimpleSearchService {

			return $this->replaceConstructorArguments(SimpleSearchService::class, [

				"ormDialect" => $this->ormDialect,

				"payloadStorage" => $this->container->getClass(PayloadStorage::class) // Without this instance, PHPUnit will not recursively wire in dependencies, thereby missing out on requestDetails
			], [], $mockMethods);
		}
	}
?>