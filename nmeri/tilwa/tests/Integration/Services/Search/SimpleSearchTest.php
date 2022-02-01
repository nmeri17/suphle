<?php
	namespace Tilwa\Tests\Integration\Services;

	use Tilwa\Testing\Condiments\{DirectHttpTest, MockFacilitator};

	use Tilwa\Testing\TestTypes\IsolatedComponentTest;

	use Tilwa\Contracts\Database\Orm;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Concretes\Services\Search\SimpleSearchService;

	use stdClass;

	class SimpleSearchTest extends IsolatedComponentTest {

		use DirectHttpTest, MockFacilitator;

		private $orm, $searchService, $baseUrl = "/search/?q=jobs&";

		public function setUp ():void {

			parent::setUp();

			$this->orm = $this->negativeStub(Orm::class);

			$this->container->whenTypeAny()->needsAny([

				Orm::class => $this->orm
			]);

			$this->searchService = $this->container->getClass(SimpleSearchService::class);
		}

		public function test_calls_class_methods_matching_queries () {
			
			$model = new stdClass;
			
			// then
			$this->orm->expects($this->never())

			->method("addWhereClause")->with($this->anything());

			$this->searchService->expects($this->once())

			->method("custom_filter")->with($this->equalTo($model), 5);

			$this->setHttpParams($this->baseUrl . "custom_filter=5"); // given

			$this->searchService->convertToQuery($model, "q"); // when
		}

		public function test_skips_class_methods_not_matching_queries () {
			
			$model = new stdClass;

			$this->searchService->expects($this->never())

			->method("custom_filter")->with($this->equalTo($model), 5); // then

			$this->setHttpParams($this->baseUrl . "database_column=foo"); // given

			$this->searchService->convertToQuery($model, "q"); // when
		}

		public function test_calls_orm_when_sees_custom_method () {
			
			$this->orm->expects($this->atLeastOnce())

			->method("addWhereClause")->with($this->anything()); // then

			$this->setHttpParams($this->baseUrl . "database_column=foo"); // given

			$this->searchService->convertToQuery($model, "q"); // when
		}
	}
?>