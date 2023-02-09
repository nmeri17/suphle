<?php
	namespace Suphle\Tests\Integration\Services\Search;

	use Suphle\Contracts\Database\OrmDialect;

	use Suphle\Request\PayloadStorage;

	use Suphle\Testing\TestTypes\ModuleLevelTest;

	use Suphle\Tests\Mocks\Modules\ModuleOne\Concretes\Services\Search\SimpleSearchService;

	use Suphle\Tests\Integration\Services\ReplacesRequestPayload;

	use stdClass;

	class SimpleSearchTest extends ModuleLevelTest {

		use ReplacesRequestPayload;

		protected object $model;

		protected array $baseQuery = ["q" => "jobs"];

		public function setUp ():void {

			parent::setUp();

			$this->model = new stdClass;
		}

		public function test_calls_class_methods_matching_queries () {

			$fieldValue = 3;

			$this->baseQuery["custom_filter"] = $fieldValue;

			$this->stubRequestObjects(3, $this->baseQuery); // given
			
			// then
			$ormDialect = $this->negativeDouble(OrmDialect::class, [], [

				"addWhereClause" => [$this->never(), [$this->anything()]]
			]);

			$searchService = $this->getSearchService([

				"custom_filter" => [1, [ $this->model, $fieldValue]]
			], $ormDialect);

			$searchService->convertToQuery($this->model, ["q"]); // when
		}

		public function test_skips_class_methods_not_matching_queries () {

			$fieldValue = "foo";

			$this->baseQuery["database_column"] = $fieldValue;

			$this->stubRequestObjects(8, $this->baseQuery); // given

			$searchService = $this->getSearchService([

				"custom_filter" => [$this->never(), [

					$this->model, $fieldValue
				]]
			], $this->negativeDouble(OrmDialect::class)); // then

			$searchService->convertToQuery($this->model, ["q"]); // when
		}

		public function test_calls_ormDialect_when_sees_custom_method () {

			$this->baseQuery["database_column"] = "foo";

			$this->stubRequestObjects(7, $this->baseQuery); // given
			
			$ormDialect = $this->negativeDouble(OrmDialect::class, [], [

				"addWhereClause" => [$this->atLeastOnce(), [$this->anything()]]
			]); // then

			$this->getSearchService([], $ormDialect)

			->convertToQuery($this->model, ["q"]); // when
		}

		protected function getSearchService (array $mockMethods = [], OrmDialect $ormDialect):SimpleSearchService {

			$sut = $this->positiveDouble(SimpleSearchService::class, [], $mockMethods);

			$sut->setPayloadStorage($this->getContainer()->getClass(PayloadStorage::class)); // Without this instance, PHPUnit will not recursively wire in dependencies, thereby missing out on requestDetails

			$sut->setOrmDialect($ormDialect);

			return $sut;
		}
	}
?>