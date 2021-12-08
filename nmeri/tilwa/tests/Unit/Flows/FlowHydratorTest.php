<?php
	namespace Tilwa\Tests\Unit\Flows;

	use Tilwa\Testing\TestTypes\IsolatedComponentTest;

	use Tilwa\Testing\Condiments\{MockFacilitator, ProphecyWrapper};

	use Prophecy\Argument\Token\InArrayToken;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Concretes\FlowService;

	class FlowHydratorTest extends IsolatedComponentTest {

	 	use ProphecyWrapper, MockFacilitator {

			ProphecyWrapper::setup as prophecySetup;
	 	};

		private $payloadKey = "data", $columnName = "id",

		$flowService = FlowService::class, $indexes;

		public function setUp () {

			parent::setUp();

			$this->prophecySetup();

			$this->indexes = $this->getIndexes();
		}

		public function test_pipeTo() {

			$sut = $this->mockFlowHydrator();

			$indexes = $this->indexes; // given

			// then
			$sut->updateRequest(new InArrayToken($indexes))

			->shouldBeCalledTimes(count($indexes));

			// when
			$sut->reveal()

			->handlePipe($indexes, 1, $this->getCollectionNode());
		}

		// Note: Always returns a new instance. Store in a variable if behavior is unwanted
		private function mockFlowHydrator () {

			$hydrator = $this->prophesize(FlowHydrator::class);

			$hydrator->executeRequest()->shouldBeCalled();

			return $hydrator;
		}

		private function getCollectionNode ():CollectionNode {

			return new CollectionNode($this->payloadKey, $this->columnName);
		}

		private function getIndexes ():array {

			$indexes = [];

			for ($i=1; $i < 11; $i++) $indexes[] = $i;

			return $indexes;
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

			// when
			$sut->reveal()

			->handleQuerySegmentAlter($payload, $leafName);
		}

		public function getPageNumbers ():array {

			return [[2], [3]];
		}

		public function test_fromService_returns_service_call_result() {

			$sut = $this->getHydratorForService(); // given

			$result = $sut->handleServiceSource(null, $this->getServiceContext(), $this->getCollectionNode() ); // when

			$flowServiceInstance = $this->container->getClass($this->flowService);

			$this->assertSame($result, $flowServiceInstance->customHandlePrevious($this->indexesToModels())); // then
		}

		private function getHydratorForService ():FlowHydrator {

			return $this->replaceConstructorArguments(FlowHydrator::class, [

				"randomContainer" => $this->container
			], [

				"getNodeFromPrevious" => [$this->payloadKey => $this->indexesToModels()]
			]);
		}

		private function indexesToModels ():array {

			return array_map(function ($id) {

				return compact("id");
			}, $this->indexes);
		}

		public function test_fromService_doesnt_edit_request_or_trigger_controller() {

			$sut = $this->prophesize(FlowHydrator::class);

			// then
			$sut->updateRequest()->shouldNotBeCalled();

			$sut->executeRequest()->shouldNotBeCalled();

			// when
			$sut->reveal()

			->handleServiceSource(null, $this->getServiceContext(), $this->getCollectionNode() );
		}

		private function getServiceContext ():ServiceContext {

			return new ServiceContext($this->flowService, "customHandlePrevious");
		}

		public function test_handleRange () {

			//
		}

		public function test_handleDateRange () {

			//
		}

		public function test_handleOneOf () {

			//
		}

		public function test_setMaxHitsHydrator () {

			// configs. call the methods on the unitNode
		}

		public function test_setExpiresAtHydrator () {

		 //
		}
		
		public function test_will_trigger_underlying_format () {

			// call `runNodes` with pre-configured unitNodes. we wanna confirm it'll hit each of the internals with the arguments they are accustomed to
			// alternatively, you might wanna use the immediate lower layer. test that one then calls the final ones (rather than going directly)
			// will need `$sut->previousResponse` set
		}
		
		public function test_executeRequest_triggers_controller () {

			//
		}

		public function test_getNodeFromPrevious() {

			//
		}
	}
?>