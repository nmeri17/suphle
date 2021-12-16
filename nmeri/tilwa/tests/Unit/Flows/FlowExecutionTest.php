<?php
	namespace Tilwa\Tests\Unit\Flows;

	use Tilwa\Testing\TestTypes\IsolatedComponentTest;

	use Tilwa\Testing\Condiments\MockFacilitator;

	use Tilwa\Flows\{FlowHydrator, Structures\RouteUserNode, Previous\SingleNode};

	use Tilwa\Response\ResponseManager;

	class FlowExecutionTest extends IsolatedComponentTest {

	 	use MockFacilitator, FlowData;

	 	private $responseManager = ResponseManager::class;

	 	public function setUp () {

			parent::setUp();

			$this->indexes = $this->getIndexes();
		}
		
		public function test_executeRequest_triggers_controller () {

			// given
			$hydrator = $this->getHydratorForExecuteRequest(true);

			$responseManager = $this->getProphet()->prophesize($this->responseManager);

			$responseManager->handleValidRequest()->shouldBeCalled();
 
 			// when
			$hydrator->setDependencies($responseManager, [])

			->executeRequest();
		}

		private function getHydratorForExecuteRequest (bool $canProcessPath):FlowHydrator {

			return $this->negativeStub(FlowHydrator::class, compact("canProcessPath"));
		}
		
		public function test_invalid_request_doesnt_trigger_controller () {

			// given
			$hydrator = $this->getHydratorForExecuteRequest(false);

			$responseManager = $this->getProphet()->prophesize($this->responseManager);

			$responseManager->handleValidRequest()->shouldNotBeCalled();
 
 			// when
			$hydrator->setDependencies($responseManager, [])

			->executeRequest();
		}

		public function test_getNodeFromPrevious() {

			$hydrator = $this->container->getClass(FlowHydrator::class);

			$models = $this->indexesToModels();

			$unitNode = new SingleNode($this->payloadKey);

			// given
			$responseManager = $this->negativeStub($this->responseManager);
			
			$hydrator->setDependencies($responseManager, [

				$this->payloadKey => $models
			]);

			$content = $hydrator->getNodeFromPrevious($unitNode); // when

			$this->assertSame($content, $models);
		}
		
		/**
		 * @dataProvider getCollectionNodes
		*/
		public function test_collection_triggers_underlying_format (CollectionNode $unitNode, string $handler, $value ) {

			$sut = $this->getHydratorForRunNode($handler, $value);

			$sut->runNodes($unitNode, "*"); // when
		}

		public function getCollectionNodes ():array {

			$serviceContext = new ServiceContext("Foo", "bar");

			return [
				[$this->createCollectionNode()->pipeTo(), "handlePipe"],

				[$this->createCollectionNode()->oneOf(), "handleOneOf", "ids"],

				[$this->createCollectionNode()->oneOf("concat"), "handleOneOf", "concat"],

				[$this->createCollectionNode()->inRange(), "handleRange"], // too lazy to extract the context from getActions

				[$this->createCollectionNode()->dateRange(), "handleDateRange"],

				[$this->createCollectionNode()->setFromService($serviceContext), "handleServiceSource", $serviceContext]
			];
		}

		private function getHydratorForRunNode (string $handler, $value = null):FlowHydrator {

			// given
			$payload = $this->payloadFromPrevious();

			$hydrator = $this->positiveStub(FlowHydrator::class, [

				"getNodeFromPrevious" => $payload,

				$handler => null
			]);

			$parameter = !is_null($value) ? $this->equalTo($value): $this->anything();

			$hydrator->expects($this->once())->method($handler)
			
			->with( $this->equalTo($payload), $parameter ); // then

			return $hydrator;
		}

		public function test_single_triggers_underlying_format () {

			$argument = "next_page_url";

			$unitNode = (new SingleNode($this->payloadKey))

			->altersQuery($argument);

			$sut = $this->getHydratorForRunNode("handleQuerySegmentAlter", $argument);

			$sut->runNodes($unitNode, "*"); // when
		}
	}
?>