<?php
	namespace Tilwa\Tests\Unit\Flows;

	use Tilwa\Flows\{FlowHydrator, Previous\CollectionNode};

	use Tilwa\Flows\Structures\{RangeContext, ServiceContext, GeneratedUrlExecution};

	use Tilwa\Testing\TestTypes\IsolatedComponentTest;

	use Tilwa\Tests\Integration\Generic\CommonBinds;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Concretes\FlowService;

	class HandlersTest extends IsolatedComponentTest {

	 	use FlowData, CommonBinds;

		private $flowService = FlowService::class,

		$sutName = FlowHydrator::class;

		public function setUp ():void {

			parent::setUp();

			$this->indexes = $this->getIndexes();
		}

		public function test_pipeTo() {

			$indexes = $this->indexes; // given

			$indexesCount = count($indexes);

			$unitNode = $this->createCollectionNode();

			// then
			$sut = $this->mockFlowHydrator([
				
				"updatePlaceholders" => [$indexesCount, [

					$this->callback(function($subject) use ($indexes, $unitNode) {

						$id = $subject[$unitNode->getLeafName()];

						return in_array($id, $indexes);
					})
				]],

				"executeGeneratedUrl" => [$indexesCount, []]
			]);

			// when
			$sut->handlePipe($indexes, 1, $unitNode);
		}

		private function mockFlowHydrator ( array $mocks):FlowHydrator {

			$mocks = array_merge(["executeGeneratedUrl" => [1, []]], $mocks);

			return $this->replaceConstructorArguments(

				$this->sutName, [], [

					"updatePlaceholders" => $this->returnSelf(),

					"updatePayloadStorage" => $this->returnSelf(),

					"executeGeneratedUrl" => $this->positiveDouble(GeneratedUrlExecution::class)
				], $mocks
			);
		}

		/**
	     * @dataProvider getPageNumbers
	     */
		public function test_handleQuerySegmentAlter(int $pageNumber) {

			// given
			$queryUpdate = ["page_number" => $pageNumber];

			// then
			$sut = $this->mockFlowHydrator([

				"updatePayloadStorage" => [1, [$queryUpdate]]
			]);

			// when
			$sut->handleQuerySegmentAlter( // suppose next page is 2 according to current outgoing request, flow runs and stores for 2

				"/posts/?" . http_build_query($queryUpdate)
			);
		}

		public function getPageNumbers ():array {

			return [[2], [3]];
		}

		public function test_fromService_returns_service_call_result() {

			$sut = $this->getHydratorForService(); // given

			$result = $sut->handleServiceSource(null, $this->getServiceContext(), $this->createCollectionNode() ); // when

			$flowServiceInstance = $this->container->getClass($this->flowService);

			$this->assertSame(
				$result,

				$flowServiceInstance->customHandlePrevious([
					
					"data" => $this->indexesToModels()
				])
			); // then
		}

		private function getHydratorForService (array $mockMethods = []):FlowHydrator {

			return $this->replaceConstructorArguments($this->sutName, [/*using this so they can receive proper containers*/], [

				"getNodeFromPrevious" => $this->payloadFromPrevious()
			], $mockMethods);
		}

		public function test_fromService_doesnt_edit_request_or_trigger_controller() {

			$sut = $this->getHydratorForService([ // then

				"executeGeneratedUrl" => [0, []],

				"updatePlaceholders" => [0, []],
			]);

			$sut->handleServiceSource( // when

				null, $this->getServiceContext(),

				$this->createCollectionNode() // given
			);
		}

		public function test_fromService_passes_previous_payload() {

			$this->container->whenTypeAny()->needsAny([ // given

				$this->flowService => $this->positiveDouble($this->flowService, [], [

					"customHandlePrevious" => [1, [

						$this->payloadFromPrevious()
					]]
				]) // then
			]);

			// when
			$this->getHydratorForService()->handleServiceSource(
				
				null, $this->getServiceContext(),

				$this->createCollectionNode()
			);
		}

		private function getServiceContext ():ServiceContext {

			return new ServiceContext($this->flowService, "customHandlePrevious");
		}

		public function test_handleOneOf () {

			$indexes = $this->indexes;

			$requestProperty = "ids"; // given

			// then
			$sut = $this->mockFlowHydrator([

				"updatePlaceholders" => [1, [

					[$requestProperty => implode(",", $indexes) ]
				]]
			]);

			// when
			$sut->handleOneOf($indexes, $requestProperty, $this->createCollectionNode());
		}

		/**
		 * @dataProvider getRegularRanges
		*/
		public function test_handleRange (RangeContext $range) {

			$range = new RangeContext;

			$indexes = $this->indexes; // given

			// then
			$sut = $this->mockFlowHydrator([

				"updatePlaceholders" => [1, [
					[
						$range->getParameterMax() => max($indexes),

						$range->getParameterMin() => min($indexes)
					]
				]]
			]);

			// when
			$sut->handleRange($indexes, $range);
		}

		public function getRegularRanges ():array {

			return [
				[new RangeContext],
				[new RangeContext(null, "min_value")]
			];
		}

		public function test_handleDateRange () {

			$range = new RangeContext;

			$maxDate = "2021-12-09";

			$minDate = "2021-01-15";

			$dates = [

				"2021-07-17", $maxDate, "2021-08-15", // deliberately scatter them

				$minDate, "2021-07-01"
			]; // given

			// then
			$sut = $this->mockFlowHydrator([

				"updatePlaceholders" => [1, [
					[
						$range->getParameterMax() => $maxDate,

						$range->getParameterMin() => $minDate
					]
				]]
			]);

			// when
			$sut->handleDateRange($dates, $range);
		}
	}
?>