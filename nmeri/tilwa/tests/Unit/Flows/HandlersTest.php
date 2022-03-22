<?php
	namespace Tilwa\Tests\Unit\Flows;

	use Tilwa\Testing\TestTypes\IsolatedComponentTest;

	use Tilwa\Testing\Condiments\MockFacilitator;

	use Prophecy\Argument\Token\InArrayToken;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Concretes\FlowService;

	use Tilwa\Flows\{FlowHydrator, Structures\RangeContext, Previous\CollectionNode};

	class HandlersTest extends IsolatedComponentTest {

	 	use MockFacilitator, FlowData;

		private $flowService = FlowService::class;

		public function setUp ():void {

			parent::setUp();

			$this->indexes = $this->getIndexes();
		}

		public function test_pipeTo() {

			$sut = $this->mockFlowHydrator();

			$indexes = $this->indexes; // given

			// then
			$sut->updateRequest(new InArrayToken($indexes))

			->shouldBeCalledTimes(count($indexes));

			$sut->executeRequest()

			->shouldBeCalledTimes(count($indexes));

			// when
			$sut->reveal()

			->handlePipe($indexes, 1, $this->createCollectionNode());
		}

		// Note: Always returns a new instance. Store in a variable if behavior is unwanted
		private function mockFlowHydrator () {

			$hydrator = $this->prophesize(FlowHydrator::class);

			$hydrator->executeRequest()->shouldBeCalled();

			return $hydrator;
		}

		/**
	     * @dataProvider getPageNumbers
	     */
		public function test_handleQuerySegmentAlter(int $pageNumber) {

			$sut = $this->mockFlowHydrator();

			// given
			$queryUpdate = ["page_number" => $pageNumber];

			$leafName = "next_page_url";

			$payload = [
				$this->payloadKey => $this->indexes,

				$leafName => "/posts/?" . http_build_query($queryUpdate)
			];

			// then
			$sut->updateRequest($queryUpdate)->shouldBeCalled();

			$sut->executeRequest()->shouldBeCalled();

			// when
			$sut->reveal()

			->handleQuerySegmentAlter($payload, $leafName);
		}

		public function getPageNumbers ():array {

			return [[2], [3]];
		}

		public function test_fromService_returns_service_call_result() {

			$sut = $this->getHydratorForService(); // given

			$result = $sut->handleServiceSource(null, $this->getServiceContext(), $this->createCollectionNode() ); // when

			$flowServiceInstance = $this->container->getClass($this->flowService);

			$this->assertSame($result, $flowServiceInstance->customHandlePrevious($this->indexesToModels())); // then
		}

		private function getHydratorForService ():FlowHydrator {

			return $this->replaceConstructorArguments(FlowHydrator::class, [

				"randomContainer" => $this->container
			], [

				"getNodeFromPrevious" => $this->payloadFromPrevious()
			]);
		}

		public function test_fromService_doesnt_edit_request_or_trigger_controller() {

			$sut = $this->prophesize(FlowHydrator::class);

			// then
			$sut->updateRequest()->shouldNotBeCalled();

			$sut->executeRequest()->shouldNotBeCalled();

			// when
			$sut->reveal()

			->handleServiceSource(null, $this->getServiceContext(), $this->createCollectionNode() );
		}

		public function test_fromService_passes_previous_payload() {

			$sut = $this->getHydratorForService();

			$mockService = $this->prophesize($this->flowService);

			// then
			$mockService

			->customHandlePrevious($this->indexesToModels())

			->shouldBeCalled();

			// given
			$this->container->whenTypeAny()->needsAny([

				$this->flowService => $mockService->reveal()
			]);

			// when
			$sut->handleServiceSource(null, $this->getServiceContext(), $this->createCollectionNode() );
		}

		private function getServiceContext ():ServiceContext {

			return new ServiceContext($this->flowService, "customHandlePrevious");
		}

		public function test_handleOneOf () {

			$sut = $this->mockFlowHydrator();

			$indexes = $this->indexes;

			$requestProperty = "ids"; // given

			// then
			$sut->updateRequest([

				$requestProperty => implode(",", $indexes)
			])
			->shouldBeCalled();

			$sut->executeRequest()->shouldBeCalled();

			// when
			$sut->reveal()

			->handleOneOf($indexes, $requestProperty, $this->createCollectionNode());
		}

		/**
		 * @dataProvider getRegularRanges
		*/
		public function test_handleRange (RangeContext $range) {

			$sut = $this->mockFlowHydrator();

			$range = new RangeContext;

			$indexes = $this->indexes; // given

			// then
			$sut->updateRequest([

				$range->getParameterMax() => max($indexes),

				$range->getParameterMin() => min($indexes)
			])
			->shouldBeCalled();

			$sut->executeRequest()->shouldBeCalled();

			// when
			$sut->reveal()->handleRange($indexes, $range);
		}

		protected function getRegularRanges ():array {

			return [
				[new RangeContext],
				[new RangeContext(null, "min_value")]
			];
		}

		public function test_handleDateRange () {

			$sut = $this->mockFlowHydrator();

			$range = new RangeContext;

			$dates = ["2021-01-15", "2021-07-01", "2021-08-15", "2021-07-17", "2021-12-09"]; // given

			// then
			$sut->updateRequest([

				$range->getParameterMax() => current($dates),

				$range->getParameterMin() => end($dates)
			])
			->shouldBeCalled();

			$sut->executeRequest()->shouldBeCalled();

			// when
			$sut->reveal()->handleDateRange($dates, $range);
		}
	}
?>