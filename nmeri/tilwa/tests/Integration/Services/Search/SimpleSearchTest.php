<?php
	namespace Tilwa\Tests\Integration\Services;

	use Tilwa\Testing\{TestTypes\IsolatedComponentTest, Condiments\DirectHttpTest};

	use Tilwa\Contracts\Database\OrmDialect;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Concretes\Services\Search\SimpleSearchService;

	use stdClass;

	class SimpleSearchTest extends IsolatedComponentTest {

		use DirectHttpTest;

		private $ormDialect, $searchService, $model,

		$baseUrl = "/search/?q=jobs&";

		public function setUp ():void {

			parent::setUp();

			$this->ormDialect = $this->negativeDouble(OrmDialect::class);

			$this->searchService = $this->positiveDouble(SimpleSearchService::class);
			
			$this->model = new stdClass;

			$this->container->whenTypeAny()->needsAny([

				OrmDialect::class => $this->ormDialect,

				SimpleSearchService::class => $this->searchService
			]);
		}

		public function test_calls_class_methods_matching_queries () {
			
			// then
			$this->mockCalls([

				"addWhereClause" => [$this->never(), [$this->anything()]]
			], $this->ormDialect);

			$this->mockCalls([

				"custom_filter" => [1, [

					$this->equalTo($this->model), 5
				]]
			], $this->searchService);

			$this->setHttpParams($this->baseUrl . "custom_filter=5"); // given

			$this->searchService->convertToQuery($this->model, "q"); // when
		}

		public function test_skips_class_methods_not_matching_queries () {

			$this->mockCalls([

				"custom_filter" => [$this->never(), [

					$this->equalTo($this->model), 5
				]]
			], $this->searchService); // then

			$this->setHttpParams($this->baseUrl . "database_column=foo"); // given

			$this->searchService->convertToQuery($this->model, "q"); // when
		}

		public function test_calls_ormDialect_when_sees_custom_method () {
			
			$this->mockCalls([

				"addWhereClause" => [$this->atLeastOnce(), [$this->anything()]]
			], $this->ormDialect); // then

			$this->setHttpParams($this->baseUrl . "database_column=foo"); // given

			$this->searchService->convertToQuery($this->model, "q"); // when
		}
	}
?>